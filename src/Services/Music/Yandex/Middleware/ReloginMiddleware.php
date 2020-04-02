<?php

declare(strict_types=1);

namespace App\Services\Music\Yandex\Middleware;

use GuzzleHttp\RequestOptions;
use Monolog\Logger;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use function GuzzleHttp\Psr7\modify_request;

class ReloginMiddleware
{
    /*
     * Total fault retries
     */
    private $queries = 0;
    /*
     * @var int
     * Allowed count of successful requests per proxy
     */
    private int $triesLimit = 3;
    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger = null;

    /**
     * @param callable $nextHandler Next handler to invoke.
     */
    public function __construct(callable $nextHandler)
    {
        $this->nextHandler = $nextHandler;
    }

    /**
     * @return \Closure
     */
    public static function create(?LoggerInterface $logger = null)
    {
        return function ($handler) use($logger) {
            return (new static($handler))->setLogger($logger);
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

        return $next($request, $options)
            ->then(
                function (ResponseInterface $response) use ($request, $options, $next) {
                    //die($request->getBody());

                    $data = \GuzzleHttp\json_decode($response->getBody(), true);
                    dd($data);

                    // TODO recognize captcha...

                    $request = modify_request($request, [
                        RequestOptions::BODY => http_build_query([
                            'x_captcha_answer' => 123213,
                            'x_captcha_key' => '',
                        ]) . '&' . $request->getBody()->__toString()
                    ]);

                    dd($request->getBody()->__toString());

                    //return $next($request, $options);


                    if($response->getStatusCode() === 403) {
                        $data = \GuzzleHttp\json_decode($response->getBody(), true);
                        // if captcha
                        // $data['x_captcha_url']
                        // $data['x_captcha_key']
                        // $data['error_description'] = CAPTCHA required
                        // $data['error'] = 403

                        dd($request);
                        // Forbidden status
                        dd(\GuzzleHttp\json_decode($response->getBody(), true));
                    }
                    dd($response);

                    return $response;
                }/*,
                function(\Exception $e) use($request, $options) {
                    return $this($request, $options);
                }*/
            );
    }

    protected function log(string $message, $level): void
    {
        if(!$this->logger) {
            return;
        }

        $this->logger->log($level, "{$this->currentProxy->getProxy()}: {$message}");
    }
}
