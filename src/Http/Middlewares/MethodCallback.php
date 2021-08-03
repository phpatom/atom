<?php


namespace Atom\Framework\Http\Middlewares;

use Atom\DI\Definition;
use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\Framework\Http\RequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

class MethodCallback extends AbstractMiddleware
{
    use DefinitionToResponseTrait;

    /**
     * @var mixed $object
     */
    private $object;
    /**
     * @var string
     */
    private string $method;
    /**
     * @var array
     */
    private array $args;
    /**
     * @var array
     */
    private array $mapping;

    public function __construct($object, string $method, array $args = [], array $mapping = [])
    {

        $this->object = $object;
        $this->method = $method;
        $this->args = $args;
        $this->mapping = $mapping;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandler $handler
     * @return ResponseInterface
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public function run(ServerRequestInterface $request, RequestHandler $handler): ResponseInterface
    {
        return self::call($this->object, $this->method, $request, $handler, $this->args, $this->mapping);
    }

    /**
     * @param $object
     * @param string $method
     * @param ServerRequestInterface $request
     * @param RequestHandler $handler
     * @param array $args
     * @param array $mapping
     * @return mixed
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public static function call(
        $object,
        string $method,
        ServerRequestInterface $request,
        RequestHandler $handler,
        array $args = [],
        array $mapping = []
    ): ?ResponseInterface {
        $definition = Definition::callTo($method)->method()->on($object);
        return self::definitionToResponse($definition, $request, $handler, $args, $mapping);
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }
}
