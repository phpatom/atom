<?php


namespace Atom\Framework\Http;

use Atom\Framework\Contracts\PipelineProcessorContract;
use Atom\Framework\Events\MiddlewareLoaded;
use Atom\Framework\Exceptions\RequestHandlerException;
use Atom\Framework\Http\Middlewares\FunctionCallback;
use Atom\Framework\Http\Middlewares\MethodCallback;
use Atom\Framework\Pipeline\Pipeline;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareProcessor implements PipelineProcessorContract
{
    private ContainerInterface $container;
    /**
     * @var RequestHandlerInterface
     */
    private RequestHandlerInterface $requestHandler;

    private ?EventDispatcherInterface $eventDispatcher = null;

    public function __construct(
        ContainerInterface $container,
        RequestHandlerInterface $requestHandler
    ) {
        $this->container = $container;
        $this->requestHandler = $requestHandler;
        if ($this->container->has(EventDispatcherInterface::class)) {
            $this->eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        }
    }

    private function dispatchMiddlewareLoaded(MiddlewareInterface $event)
    {
        if ($this->eventDispatcher != null) {
            $this->eventDispatcher->dispatch(new MiddlewareLoaded($event));
        }
    }

    /**
     * @param $data
     * @param $handler
     * @param Pipeline $pipeline
     * @return ResponseInterface|ServerRequestInterface
     * @throws RequestHandlerException
     */
    public function process($data, $handler, Pipeline $pipeline): ResponseInterface
    {
        if ($data instanceof ResponseInterface) {
            return $data;
        }
        $middleware = $this->getMiddleware($handler);
        $this->dispatchMiddlewareLoaded($middleware);
        return $middleware->process($data, $this->requestHandler->setPipeline($pipeline));
    }

    /**
     * @param $arg
     * @return MiddlewareInterface
     * @throws RequestHandlerException
     */
    public function getMiddleware($arg): MiddlewareInterface
    {
        if ($arg instanceof MiddlewareInterface) {
            return $arg;
        }
        if (is_string($arg)) {
            return $this->container->get($arg);
        }

        if (is_callable($arg) && !is_array($arg)) {
            return new FunctionCallback($arg);
        }
        if ((is_array($arg) && (count($arg) == 2) && isset($arg[0]) && isset($arg[1]))) {
            return new MethodCallback($arg[0], $arg[1]);
        }
        $name = gettype($arg);
        if (is_object($arg)) {
            $name = get_class($arg);
        }
        throw new RequestHandlerException("The middleware 
                [$name] is not valid");
    }

    public function shouldStop($result): bool
    {
        return ($result instanceof ResponseInterface);
    }
}
