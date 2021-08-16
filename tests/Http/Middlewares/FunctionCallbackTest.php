<?php


namespace Atom\Framework\Test\Http\Middlewares;

use Atom\Framework\Application;
use Atom\Framework\Http\Middlewares\FunctionCallback;
use Atom\Framework\Http\Request;
use Atom\Framework\Http\Response;
use Laminas\Diactoros\Response\HtmlResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class FunctionCallbackTest extends TestCase
{
    public function testRun()
    {
        $app = Application::create(__DIR__);
        $handler = $app->requestHandler();
        $request = new Request();

        $middleware = new FunctionCallback(
            function (string $foo, ResponseInterface $response) {
                $body = (string)$response->getBody();
                return $foo . $body;
            },
            ["foo" => "foo"],
            [ResponseInterface::class => Response::text("bar")]
        );
        $handler->add($middleware);
        $response = $handler->handle($request);
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals("foobar", (string)$response->getBody());
    }
}
