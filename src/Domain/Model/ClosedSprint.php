<?php
declare(strict_types=1);

namespace App\Domain\Model;

readonly class ClosedSprint extends Sprint
{
    public function __construct(
        int         $id,
        string      $name,
        private int $velocity,
    )
    {
        parent::__construct($id, $name);
    }


    public function velocity(): int
    {
        return $this->velocity;
    }
}