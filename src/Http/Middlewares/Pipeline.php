<?php

namespace Atom\Framework\Http\Middlewares;

use Atom\Framework\Http\RequestHandler;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Pipeline extends AbstractMiddleware
{

    /**
     * @var array
     */
    private array $middlewares;

    public function __construct(array $middlewares)
    {
        $this->middlewares = $middlewares;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandler $handler
     * @return ResponseInterface
     * @throws Exception
     */
    public function run(ServerRequestInterface $request, RequestHandler $handler): ResponseInterface
    {
        return $handler
            ->withNext($this->middlewares)
            ->handle($request);
    }

    public static function create(array $middlewares): Pipeline
    {
        return new self($middlewares);
    }
}
