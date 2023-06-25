<?php
declare(strict_types=1);

namespace App\Domain\Ports\Outbound;

interface IAbsenceProvider
{
    public function getVacationDays(): int;
    public function getSickDays(): int;
}
