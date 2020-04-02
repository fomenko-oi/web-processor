<?php

declare(strict_types=1);

namespace App\Services\Common\Guzzle\Middleware;

class Proxy
{
    /**
     * @var string
     */
    private string $proxy;
    /**
     * @var int
     */
    private int $limit;
    /**
     * @var int
     */
    private int $requestsCount = 0;

    public function __construct(string $proxy, $limit = 1)
    {
        $this->proxy = $proxy;
        $this->limit = $limit;
    }

    public function incrementRequestsCount(): void
    {
        $this->requestsCount++;
    }

    public function isExhausted(): bool
    {
        return $this->requestsCount > $this->limit;
    }

    public function getProxy(): string
    {
        return $this->proxy;
    }

    public function getRequestsCount(): int
    {
        return $this->requestsCount;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function __toString(): string
    {
        return $this->proxy;
    }
}
