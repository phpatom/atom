<?php


namespace Atom\Framework\Test\Http\Middlewares;

use Atom\DI\Container;
use Atom\Framework\Http\Middlewares\CallableMiddleware;
use Atom\Framework\Http\Middlewares\Middleware;
use Atom\Framework\Http\Middlewares\Pipeline;
use Atom\Framework\Http\Middlewares\ResponseMiddleware;
use Atom\Framework\Http\Request;
use Atom\Framework\Http\RequestHandler;
use Laminas\Diactoros\Response\EmptyResponse;
use PHPUnit\Framework\TestCase;

class MiddlewareTest extends TestCase
{
    public function testFunction()
    {
        $res = new EmptyResponse();
        $callable = fn() => $res;
        $middleware = Middleware::callable($callable);
        $this->assertInstanceOf(CallableMiddleware::class, $middleware);
        $this->assertEquals(
            $res,
            $middleware->process(new Request(), new RequestHandler(
                new Container()
            ))
        );
    }

    public function testResponse()
    {
        $res = new EmptyResponse();
        $middleware = Middleware::response($res);
        $this->assertInstanceOf(ResponseMiddleware::class, $middleware);
        $this->assertEquals(
            $res,
            $middleware->process(new Request(), new RequestHandler(
                new Container()
            ))
        );
    }

    /**
     * @throws \Exception
     */
    public function testPipeline()
    {
        $res = new EmptyResponse();
        $middleware = Middleware::pipeline([
            Middleware::response($res)
        ]);
        $this->assertInstanceOf(Pipeline::class, $middleware);
        $handler = new RequestHandler(
            new Container(),
            [$middleware]
        );
        $result = $handler->handle(new Request());
        $this->assertEquals(
            $res,
            $result
        );
    }

}
