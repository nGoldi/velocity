<?php
declare(strict_types=1);

namespace App\Domain\Model;

use DatePeriod;
use DateTimeImmutable;

readonly class Sprint
{
    public function __construct(
        private int $id,
        private int $velocity,
        private DatePeriod $sprintDuration,
    )
    {
    }

    public function getSprintDuration(): DatePeriod
    {
        return $this->sprintDuration;
    }

    public function hasOpenCodeFriday(): bool
    {
        $month = $this->sprintDuration->getStartDate()->format('m');
        $year = $this->sprintDuration->getEndDate()?->format('Y');

        $firstFriday = new DateTimeImmutable("first friday of $year-$month");

        return $firstFriday >= $this->sprintDuration->getStartDate() && $firstFriday <= $this->sprintDuration->getEndDate();
    }

    public function id(): int
    {
        return $this->id;
    }

    public function getVelocity(): int
    {
        return $this->velocity;
    }
}