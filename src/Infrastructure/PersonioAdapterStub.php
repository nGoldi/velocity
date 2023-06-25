<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Ports\Outbound\IAbsenceProvider;
use DatePeriod;
use DateTime;

/**
 * As long as no API key is available, data can be added here manually.
 */
class PersonioAdapterStub implements IAbsenceProvider
{
    // some date in sprint => vacation days
    private const VACATION_DAYS = [
        '2023-05-13' => 4,
        '2023-05-27' => 5,
        '2023-06-09' => 4,
        '2023-06-21' => 13,
        '2023-07-07' => 2,
    ];

    public function getVacationDays(DatePeriod $period): int
    {
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');

            if (isset(self::VACATION_DAYS[$formattedDate])) {
                return self::VACATION_DAYS[$formattedDate];
            }
        }

        return 0;
    }

    public function getSickDays(DatePeriod $period): int
    {
        return 0;
    }
}
