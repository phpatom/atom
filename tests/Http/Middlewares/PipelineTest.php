<?php

namespace Atom\Framework\Test\Http\Middlewares;

use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\Event\Exceptions\ListenerAlreadyAttachedToEvent;
use Atom\Framework\Application;
use Atom\Framework\Http\Middlewares\Middleware;
use Atom\Framework\Http\Middlewares\Pipeline;
use Atom\Framework\Http\Request;
use Atom\Framework\Http\RequestHandler;
use Atom\Framework\Http\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;
use Throwable;

class PipelineTest extends TestCase
{
    /**
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws NotFoundException
     * @throws ListenerAlreadyAttachedToEvent
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testRun()
    {
        $middleware1 = Middleware::callable(function (
            ServerRequestInterface $req,
            RequestHandler $hand
        ) {
            return $hand->handle($req->withAttribute(
                "foo",
                "bar"
            ));
        });
        $middleware2 = Middleware::callable(function (
            ServerRequestInterface $req,
            RequestHandler $hand
        ) {
            return $hand->handle($req->withAttribute(
                "bar",
                "baz"
            ));
        });
        $app = Application::create(__DIR__);
        $app->add(
            new Pipeline([
                $middleware1,
                $middleware2,
                Middleware::response($res = Response::empty())
            ])
        );
        $result = $app->handle(new Request());
        $this->assertEquals($res, $result);
        $this->assertEquals([
            "foo" => "bar",
            "bar" => "baz"
        ], $app->requestHandler()->getCurrentRequest()->getAttributes());
    }

    public function testCreate()
    {
        $this->assertInstanceOf(
            Pipeline::class,
            $pipeline = Pipeline::create($middlewares = ["foo", "bar"])
        );
        $this->assertEquals(
            $pipeline->getMiddlewares(),
            $middlewares
        );
    }
}
