<?php

declare(strict_types=1);

namespace App\Infrastructure;

interface AggregateRoot
{
    public function releaseEvents(): array;
}
