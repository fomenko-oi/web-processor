<?php

declare(strict_types=1);

namespace App\Event\Dispatcher;

interface EventDispatcher
{
    public function dispatch(array $events): void;
}
