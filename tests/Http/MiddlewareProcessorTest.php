<?php


namespace Atom\Framework\Test\Http;

use Atom\Framework\Application;
use Atom\Framework\Exceptions\RequestHandlerException;
use Atom\Framework\Http\MiddlewareProcessor;
use Atom\Framework\Http\Middlewares\FunctionCallback;
use Atom\Framework\Http\Middlewares\MethodCallback;
use Atom\Framework\Pipeline\Pipeline;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use stdClass;

class MiddlewareProcessorTest extends TestCase
{
    public function makeProcessor(): MiddlewareProcessor
    {
        $app = Application::create("foo");
        return new MiddlewareProcessor($app->container(), $app->requestHandler());
    }

    public function makePipeline()
    {
        return $this->getMockBuilder(Pipeline::class)
            ->disableOriginalConstructor()->getMock();
    }

    public function testItValidatesMiddleware()
    {
        $this->expectException(RequestHandlerException::class);
        $mockPipeline = $this->makePipeline();
        $processor = $this->makeProcessor();
        $processor->process(1, 1, $mockPipeline);
    }
    public function testItValidatesMiddlewareInvalidObject()
    {
        $this->expectException(RequestHandlerException::class);
        $mockPipeline = $this->makePipeline();
        $processor = $this->makeProcessor();
        $processor->process(1, new stdClass(), $mockPipeline);
    }

    public function testProcess()
    {
        $mockPipeline = $this->makePipeline();
        $processor = $this->makeProcessor();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $res = $processor->process($response, "foo", $mockPipeline);
        $this->assertEquals($res, $response);
    }

    public function testGetMiddleware()
    {
        $mockMiddleware = $this->getMockBuilder(MiddlewareInterface::class)->getMock();
        $processor = $this->makeProcessor();
        $middleware = $processor->getMiddleware($mockMiddleware);
        $this->assertEquals($middleware, $mockMiddleware);

        $app = Application::create("foo");
        $processor = new MiddlewareProcessor($container = $app->container(), $app->requestHandler());
        $container->bind("foo")->toObject($mockMiddleware);
        $middleware = $processor->getMiddleware("foo");
        $this->assertEquals($middleware, $mockMiddleware);

        $middleware = $processor->getMiddleware(function () {
            return "baz";
        });
        $this->assertInstanceOf(FunctionCallback::class, $middleware);
        $this->assertEquals("baz", $middleware->getCallback()());

        $middleware = $processor->getMiddleware(["bar","baz"]);
        $this->assertInstanceOf(MethodCallback::class, $middleware);
        $this->assertEquals("bar", $middleware->getObject());
        $this->assertEquals("baz", $middleware->getMethod());
    }
}
