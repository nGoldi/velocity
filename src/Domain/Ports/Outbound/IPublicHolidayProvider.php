<?php
declare(strict_types=1);

namespace App\Domain\Ports\Outbound;

interface IPublicHolidayProvider
{
    public function numberOfPublicHolidays(\DatePeriod $datePeriod): int;
}