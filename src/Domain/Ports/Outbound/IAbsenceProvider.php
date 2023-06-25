<?php
declare(strict_types=1);

namespace App\Domain\Ports\Outbound;

use DatePeriod;

interface IAbsenceProvider
{
    public function getVacationDays(DatePeriod $period): int;
    public function getSickDays(DatePeriod $period): int;
}
