<?php
declare(strict_types=1);

namespace App\Domain\Ports\Outbound;

use App\Domain\Model\Sprint;

interface ISprintProvider
{
    /**
     * @return Sprint[]
     */
    public function getClosedSprints(): array;
    public function getNextSprint(): Sprint;
}