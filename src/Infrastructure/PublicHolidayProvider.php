<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Ports\Outbound\IPublicHolidayProvider;
use DatePeriod;
use Yasumi\Yasumi;

class PublicHolidayProvider implements IPublicHolidayProvider
{
    public function numberOfPublicHolidays(DatePeriod $datePeriod): int // todo Do this based on employee location
    {
        $year = $datePeriod->getStartDate()->format('Y');

        $holidayProvider = Yasumi::create('Germany/BadenWurttemberg', (int)$year, 'de_DE');
        $holidays = $holidayProvider->between($datePeriod->getStartDate(), $datePeriod->getEndDate());

        return $holidays->count();
    }
}
