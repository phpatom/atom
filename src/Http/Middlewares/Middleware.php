<?php


namespace Atom\Framework\Http\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

class Middleware
{
    public static function callable(callable $callable, ?string $name = null): MiddlewareInterface
    {
        return new CallableMiddleware($callable, $name);
    }

    public static function pipeline(array $middlewares): Pipeline
    {
        return new Pipeline($middlewares);
    }

    public static function response(ResponseInterface $response): MiddlewareInterface
    {
        return new ResponseMiddleware($response);
    }
}
