<?php

declare(strict_types=1);

namespace App\Services\Common\Guzzle\Middleware;

use Monolog\Logger;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class ProxyPoolMiddleware
{
    /*
     * @var int
     * Total fault queries
     */
    private $queries = 0;
    /*
     * @var int
     * Allowed count of successful requests per proxy
     */
    private int $proxyRequestsLimit = 3;
    /**
     * @var array
     */
    private $proxyList = [];
    /**
     * @var Proxy|null
     */
    private ?Proxy $currentProxy = null;
    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger = null;

    /**
     * @param callable $nextHandler Next handler to invoke.
     */
    public function __construct(callable $nextHandler, array $proxyList)
    {
        $this->nextHandler = $nextHandler;

        shuffle($proxyList);
        $this->proxyList = $proxyList;
    }

    /**
     * @return \Closure
     */
    public static function create(array $proxyList = [], ?LoggerInterface $logger = null)
    {
        return function ($handler) use($proxyList, $logger) {
            return (new static($handler, $proxyList))->setLogger($logger);
        };
    }

    public function setLogger(?LoggerInterface $logger = null): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @param array $options
     * @return \Psr\Http\Message\RequestInterface
     */
    public function __invoke(RequestInterface $request, array $options = [])
    {
        $next = $this->nextHandler;

        $options['proxy'] = $this->resolveProxy();

        if($this->currentProxy) {
            $this->currentProxy->incrementRequestsCount();
            $this->log("Request {$this->currentProxy->getRequestsCount()} to: {$request->getUri()}", Logger::INFO);
        }

        return $next($request, $options)
            ->then(
                function (ResponseInterface $response) use ($request, $options) {
                    $this->log("Success response, {$response->getStatusCode()}", Logger::INFO);
                    return $response;
                },
                function(\Exception $e) use($request, $options) {
                    $this->log($e->getMessage(), Logger::ERROR);
                    $this->nextProxy();
                    return $this($request, $options);
                }
            );
    }

    protected function nextProxy(): void
    {
        $this->currentProxy = new Proxy(array_shift($this->proxyList), $this->proxyRequestsLimit);
    }

    protected function resolveProxy(): ?Proxy
    {
        // TODO create strategy for proxy
        if(!$this->currentProxy) {
            $this->currentProxy = new Proxy(array_shift($this->proxyList), $this->proxyRequestsLimit);
        }

        if($this->currentProxy->isExhausted()) {
            $this->currentProxy = new Proxy(array_shift($this->proxyList), $this->proxyRequestsLimit);
        }

        return $this->currentProxy;
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @param array $options
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \Psr\Http\Message\RequestInterface|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    protected function checkResponse(RequestInterface $request, array $options = [], ResponseInterface $response)
    {
        if($this->queries == 3) {
            throw new \Exception("You made 3 tries. So sad.");
        }

        if ($response->getStatusCode() !== 403) {
            $this->logger->info("Success request {$this->proxyService->getLastProxy()->getProxy()}");

            return $response;
        }

        $options['proxy'] = $this->getNextProxy();

        return $this($request, $options);
    }

    private function getNextProxy()
    {
        $projectId = $this->proxyService->getLastProxy()->project_id;

        /* @var Proxy $nextProxy */
        $nextProxy = $this->proxyService->random($projectId);

        // should to change a proxy
        $this->queries++;
        $this->logger->alert("set new proxy: {$nextProxy->getProxy()}. Total queries {$this->queries}");

        $this->usedProxy[] = $nextProxy->host;

        return $nextProxy->getProxy();
    }

    protected function log(string $message, $level): void
    {
        if(!$this->logger) {
            return;
        }

        $this->logger->log($level, "{$this->currentProxy->getProxy()}: {$message}");
    }
}
