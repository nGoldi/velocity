<?php
declare(strict_types=1);

namespace App\Domain\Ports\Inbound;

interface IVelocity
{
    public function nextSprintVelocity(): int;
}