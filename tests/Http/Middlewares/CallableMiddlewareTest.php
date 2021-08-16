<?php


namespace Atom\Framework\Test\Http\Middlewares;

use Atom\DI\Container;
use Atom\Framework\Http\Middlewares\CallableMiddleware;
use Atom\Framework\Http\Request;
use Atom\Framework\Http\RequestHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;

class CallableMiddlewareTest extends TestCase
{
    public function testProcess()
    {
        $request = new Request();
        $handler = new RequestHandler(new Container());

        $mock = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $mock->expects($this->once())->method("process")->with($request, $handler);
        $handler->add(new CallableMiddleware([$mock, "process"]));
        $handler->handle($request);
    }
}
