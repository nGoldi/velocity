<?php
declare(strict_types=1);

namespace App\Domain\Ports\Outbound;

use App\Domain\Model\ClosedSprint;
use App\Domain\Model\FutureSprint;

interface ISprintProvider
{
    /**
     * @return ClosedSprint[]
     */
    public function getClosedSprints(): array;
    public function getNextSprint(): FutureSprint;
}