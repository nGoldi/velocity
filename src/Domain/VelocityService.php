<?php
declare(strict_types=1);

namespace App\Domain;

use App\Domain\Model\Sprint;
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
        $capacityNextSprint = $this->calculateSprintCapacity($this->sprintProvider->getNextSprint());

        return (int)round($averageVelocity * ($capacityNextSprint / $this->getPlannedWorkingDays()));
    }

    private function calculateAverageVelocity(int $numberOfSprints = 3): int
    {
        $total = 0;
        foreach ($this->get($numberOfSprints) as $sprint) {
            $velocity = $sprint->getVelocity();
            $capacity = $this->calculateSprintCapacity($sprint);
            $total += $velocity / ($capacity / $this->getPlannedWorkingDays());
        }

        return (int)round($total / $numberOfSprints);
    }

    /**
     * @param int $numberOfSprints
     * @return Sprint[]
     */
    private function get(int $numberOfSprints): array
    {
        $sprints = $this->sprintProvider->getClosedSprints();
        usort($sprints, static function(Sprint $a, Sprint $b) {
            return $b->id() - $a->id();
        });

        return array_slice($sprints, 0, $numberOfSprints);
    }

    private function calculateSprintCapacity(Sprint $sprint): int
    {
        return $this->getPlannedWorkingDays() - $this->calculateTotalAbsences($sprint);
    }

    private function getPlannedWorkingDays(): int // todo calculate this based on the team members
    {
        // number of team members * working days in a sprint
        return 70;
    }

    private function calculateTotalAbsences(Sprint $sprint): int
    {
        $sprintDuration = $sprint->getSprintDuration();
        // todo conflict resolution. eg open code friday and public holiday etc
        $absence = $this->absenceProvider->getVacationDays($sprintDuration) + $this->absenceProvider->getSickDays($sprintDuration);

        $publicHolidays = $this->publicHolidayProvider->numberOfPublicHolidays($sprintDuration);
        if ($publicHolidays) {
            $absence += $publicHolidays * 6;
        }

        if ($sprint->hasOpenCodeFriday()) {
            $absence += 6;
        }

        return $absence;
    }
}
