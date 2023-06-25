<?php
declare(strict_types=1);

namespace App\Domain\Model;

abstract readonly class Sprint
{
    public function __construct(
        private int $id,
        private string $name,
    )
    {
    }

    public function id(): int
    {
        return $this->id;
    }
}