<?php


namespace Atom\Framework\Http\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CallableMiddleware implements MiddlewareInterface
{
    /**
     * @var callable
     */
    private $callable;
    private ?string $name;

    public function __construct(callable $callable, ?string $name = null)
    {
        $this->callable = $callable;
        $this->name = $name;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return call_user_func_array($this->callable, [$request, $handler]);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}
