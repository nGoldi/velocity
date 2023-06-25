<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Ports\Outbound\IAbsenceProvider;

/**
 * As long as no API key is available, data can be added here manually.
 */
readonly class PersonioAdapterStub implements IAbsenceProvider
{

    public function getVacationDays(): int
    {
        return 10;
    }

    public function getSickDays(): int
    {
        return 0;
    }
}