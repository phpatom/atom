<?php


namespace Atom\Framework\Http;

use Atom\Framework\Contracts\PipelineProcessorContract;
use Atom\Framework\Exceptions\RequestHandlerException;
use Atom\Framework\Http\Middlewares\FunctionCallback;
use Atom\Framework\Http\Middlewares\MethodCallback;
use Atom\Framework\Pipeline\Pipeline;
use Psr\Container\ContainerInterface;
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

    public function __construct(
        ContainerInterface $container,
        RequestHandlerInterface $requestHandler
    ) {
        $this->container = $container;
        $this->requestHandler = $requestHandler;
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
        return $this->getMiddleware($handler)
            ->process($data, $this->requestHandler->setPipeline($pipeline));
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
