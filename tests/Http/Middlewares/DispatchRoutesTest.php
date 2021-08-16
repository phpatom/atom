<?php


namespace Atom\Framework\Test\Http\Middlewares;

use Atom\DI\Container;
use Atom\Framework\Http\Middlewares\DispatchRoutes;
use Atom\Framework\Http\Request;
use Atom\Framework\Http\RequestHandler;
use Atom\Framework\Http\Response;
use Atom\Routing\RouteGroup;
use Atom\Routing\Router;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;

class DispatchRoutesTest extends TestCase
{

    public function testRoutesAreDispatched()
    {

        $handler = new RequestHandler(new Container());

        $routeMiddleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $routeMiddleware->expects($this->exactly(2))->method("process");
        $routeMiddleware->method("process")->willReturnCallback(function () {
            return Response::text("foo");
        });

        $groupMiddleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $groupMiddleware->method("process")->willReturnCallback(function ($req, $hand) {
            return $hand->handle($req);
        });
        $groupMiddleware->expects($this->once())->method("process");

        $router = new Router();
        $router->get("/foo", $routeMiddleware);
        $router->group("/bar", function (RouteGroup $routeGroup) use ($routeMiddleware) {
            $routeGroup->get("/baz", $routeMiddleware);
        }, $groupMiddleware);

        $handler->add(new DispatchRoutes($router));
        $handler->handle((new Request())->withUri(
            new Uri("https://localhost:8000/foo")
        ));
        $handler->reset()->handle((new Request())->withUri(
            new Uri("https://localhost:8000/bar/baz")
        ));
    }
}
