<?php


namespace Atom\Framework\Http;

use Atom\DI\Container;
use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\MultipleBindingException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\Framework\Contracts\EmitterContract;
use Atom\Framework\Contracts\HasKernel;
use Atom\Framework\Kernel;
use Atom\Framework\Pipeline\Pipeline;
use Atom\Framework\Pipeline\PipelineFactory;
use Atom\Routing\Contracts\RouterContract;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;
use RuntimeException;

class RequestHandler implements RequestHandlerInterface, HasKernel
{
    /**
     * @var ContainerInterface | Container
     */
    private ContainerInterface $container;

    private bool $started = false;

    private ?Pipeline $pipeline = null;
    private PipelineFactory $pipelineFactory;

    /**
     * RequestHandler constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->pipelineFactory = (new PipelineFactory())
            ->via(new MiddlewareProcessor($container, $this));
    }

    /**
     * @return Kernel
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function getKernel(): Kernel
    {
        return $this->container()->get(Kernel::class);
    }

    /**
     * @return ContainerInterface | Container
     */
    public function container(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return RouterContract
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function router(): RouterContract
    {
        return $this->container->get(RouterContract::class);
    }

    /**
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws Exception
     */
    public function run()
    {
        $response = $this->handle(Request::incoming());
        $this->emit($response);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->ensureStarted($request)) {
            return $this->pipeline->next();
        }
        $alteredRequest = $request;
        return $this->pipeline
            ->with($alteredRequest)
            ->next();
    }

    public function add($middleware): RequestHandler
    {
        $this->pipelineFactory->add($middleware);
        return $this;
    }

    public function middlewares($middleware): RequestHandler
    {
        $this->pipelineFactory->addPipes($middleware);
        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @return bool
     * @throws MultipleBindingException
     */
    private function ensureStarted(ServerRequestInterface $request): bool
    {
        if ($this->started) {
            return true;
        }
        $this->pipeline = $this->pipelineFactory
            ->pipe($request)
            ->make();
        $this->container()
            ->bind([ServerRequestInterface::class, get_class($request)])
            ->toObject($request);
        $this->started = true;
        return false;
    }

    /**
     * @param ResponseInterface $response
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function emit(ResponseInterface $response): void
    {
        /**
         * @var EmitterContract $emitter
         */
        $emitter = $this->container->get(EmitterContract::class);
        $emitter->emit($response);
    }

    /**
     * @return EmitterContract
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function emitter(): EmitterContract
    {
        return $this->container->get(EmitterContract::class);
    }

    /**
     * @param array $middlewares
     * @return $this
     */
    public function withNext(array $middlewares): RequestHandler
    {
        if (is_null($this->pipeline)) {
            throw  new RuntimeException("You cannot alter the request handler pipeline if it has not started yet");
        }
        $this->pipeline = $this->pipeline
            ->withAddedPipes($middlewares, $this->pipeline->getNextIndex());
        return $this;
    }

    public function getFactoryPipes(): array
    {
        return $this->pipelineFactory->getPipes();
    }

    /**
     * @param Pipeline $pipeline
     * @return RequestHandler
     */
    public function setPipeline(Pipeline $pipeline): RequestHandler
    {
        $this->pipeline = $pipeline;
        return $this;
    }

}
