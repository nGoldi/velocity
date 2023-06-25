<?php
declare(strict_types=1);

namespace App\Domain;

use App\Domain\Model\ClosedSprint;
use App\Domain\Model\FutureSprint;
use App\Domain\Ports\Inbound\IVelocity;
use App\Domain\Ports\Outbound\IAbsenceProvider;
use App\Domain\Ports\Outbound\ISprintProvider;
use App\Domain\Ports\Outbound\IPublicHolidayProvider;

readonly class VelocityService implements IVelocity
{
    public function __construct(
        private ISprintProvider  $sprintProvider,
        private IAbsenceProvider $absenceProvider,
        private IPublicHolidayProvider $publicHolidayProvider
    )
    {}

    public function nextSprintVelocity(): int
    {
        $averageVelocity = $this->calculateAverageVelocity();
        $capacity = $this->calculateCapacity();

        // todo scale past velocity to full capacity

        return 1;
    }

    private function calculateAverageVelocity(int $numberOfSprints = 3): float
    {
        $total = 0;
        foreach ($this->get($numberOfSprints) as $sprint) {
            $total += $sprint->velocity();
        }

        return round($total / $numberOfSprints);
    }

    /**
     * @param int $numberOfSprints
     * @return ClosedSprint[]
     */
    private function get(int $numberOfSprints): array
    {
        $sprints = $this->sprintProvider->getClosedSprints();
        usort($sprints, static function(ClosedSprint $a, ClosedSprint $b) {
            return $b->id() - $a->id();
        });

        return array_slice($sprints, 0, $numberOfSprints);
    }

    private function calculateCapacity(): int
    {
        return $this->getPlannedWorkingDays() - $this->absences();
    }

    private function getPlannedWorkingDays(): int // todo calculate this based on the team members
    {
        // number of team members * working days in a sprint
        return 60;
    }

    private function absences(): int
    {
        // todo conflict resolution. eg open code friday and public holiday etc
        $absence = $this->absenceProvider->getVacationDays() + $this->absenceProvider->getSickDays();
        $nextSprint = $this->sprintProvider->getNextSprint();

        $publicHolidays = $this->publicHolidayProvider->numberOfPublicHolidays($nextSprint->getSprintDuration());
        if ($publicHolidays) {
            $absence += $publicHolidays * 6;
        }

        if ($nextSprint->hasOpenCodeFriday()) {
            $absence += 6;
        }

        return $absence;
    }
}
