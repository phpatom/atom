<?php


namespace Atom\Framework\Test\Http;

use Atom\Framework\Contracts\EmitterContract;
use Atom\Framework\Http\Middlewares\Middleware;
use Atom\Framework\Http\NoMiddlewareException;
use Atom\Framework\Http\Request;
use Atom\Framework\Http\RequestHandler;
use Atom\Framework\Http\Response;
use Atom\Framework\Kernel;
use Atom\Routing\Contracts\RouterContract;
use Atom\Routing\Router;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RequestHandlerTest extends TestCase
{
    public function makehandler(?RouterContract $router = null): RequestHandler
    {
        $kernel = new Kernel("foo");
        $container = $kernel->container();
        $container->bind(RouterContract::class, $router ?? new Router());
        return new RequestHandler($container);
    }

    public function testItThrowsIfThereIsNotMiddleware()
    {
        $this->expectException(NoMiddlewareException::class);
        $handler = $this->makehandler();
        $handler->handle(new Request());
    }

    public function testRouter()
    {
        $router = new Router();
        $this->assertEquals(
            $router,
            $this->makehandler($router)->router()
        );
    }

    public function testKernel()
    {
        $kernel = new Kernel("foo");
        $kernel->container()->bind(RouterContract::class, new Router());
        $kernel->container()->bind(Kernel::class, $kernel);

        $handler = new RequestHandler($kernel->container());
        $this->assertEquals(
            $kernel,
            $handler->getKernel()
        );
    }

    public function testRun()
    {
        $kernel = new Kernel("foo");
        $container = $kernel->container();
        $mockEmitter = $this->getMockBuilder(EmitterContract::class)->getMock();
        $response = Response::text("foo", 404);
        $mockEmitter->expects($this->once())->method("emit")->with($response);
        $container->bind(EmitterContract::class, $mockEmitter);
        $handler = new RequestHandler($container);
        $handler->add(Middleware::response($response));
        $handler->run();
    }

    public function testHandle()
    {
        $kernel = new Kernel("foo");
        $container = $kernel->container();
        $handler = new RequestHandler($container);
        $request = new Request();
        $handler->middlewares([
            Middleware::callable(function ($req, $hand) {
                return $hand->handle($req->withAttribute("message", "foo"));
            }),
            Middleware::callable(function ($req, $hand) {
                return Response::text($req->getAttribute("message"), 404);
            }),
        ]);
        $res = $handler->handle($request);
        $this->assertEquals("foo", (string)$res->getBody());
        $this->assertEquals(404, $res->getStatusCode());
    }

    public function testWithNext()
    {
        $kernel = new Kernel("foo");
        $container = $kernel->container();
        $handler = new RequestHandler($container);
        $request = new Request();
        $handler->middlewares([
            Middleware::callable(function (Request $req, RequestHandler $handler) {
                $handler->withNext([
                    Middleware::callable(function ($req, RequestHandler $handler) {
                        $handler->withNext([
                            Middleware::callable(function (Request $req, RequestHandler $handler) {
                                return $handler->handle(
                                    $req->withAttribute("body2", "bar")
                                );
                            }, "2")
                        ]);
                        return $handler->handle($req->withAttribute("status", 401));
                    }, "1"), Middleware::callable(function ($req, $handler) {
                        return Response::text(
                            $req->getAttribute("body") . $req->getAttribute("body2"),
                            $req->getAttribute("status")
                        );
                    }, "3"),]);
                return $handler->handle($req->withAttribute("body", "foo"));
            }, "0"),
        ]);
        $res = $handler->handle($request);
        $this->assertEquals("foobar", (string)$res->getBody());
        $this->assertEquals(401, $res->getStatusCode());
        $this->expectException(RuntimeException::class);
        $handler = new RequestHandler($container);
        $handler->withNext([]);
    }
}
