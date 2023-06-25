<?php
declare(strict_types=1);

namespace App\Domain\Model;

use DatePeriod;
use DateTimeImmutable;

readonly class FutureSprint extends Sprint
{
    public function __construct(
        int                $id,
        string             $name,
        private DatePeriod $sprintDuration,
    )
    {
        parent::__construct($id, $name);
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
}