<?php


namespace Atom\Framework\Test\Http;

use Atom\Framework\Application;
use Atom\Framework\Http\JsonStringResponse;
use Atom\Framework\Http\Request;
use Atom\Framework\Http\Response;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\TextResponse;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testJsonResponse()
    {
        $response = Response::json($data = ["foo" => "bar"], 404, [
            "foo" => "bar"
        ], 0);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals("bar", $response->getHeaderLine("foo"));
        $this->assertEquals((string)$response->getBody(), json_encode($data, 0));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testHtmlResponse()
    {
        $response = Response::html($data = "<p>foo</p>", 404, [
            "foo" => "bar"
        ]);
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals("bar", $response->getHeaderLine("foo"));
        $this->assertEquals((string)$response->getBody(), $data);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testTextResponse()
    {
        $response = Response::text($data = "foo", 404, [
            "foo" => "bar"
        ]);
        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertEquals("bar", $response->getHeaderLine("foo"));
        $this->assertEquals((string)$response->getBody(), $data);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testEmptyResponse()
    {
        $response = Response::empty(404, [
            "foo" => "bar"
        ]);
        $this->assertInstanceOf(EmptyResponse::class, $response);
        $this->assertEquals("bar", $response->getHeaderLine("foo"));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testRedirectResponse()
    {
        $response = Response::redirect($to = "https://example.com", 301, [
            "foo" => "bar"
        ]);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals($to, $response->getHeaderLine("location"));
        $this->assertEquals(301, $response->getStatusCode());
    }

    public function testRedirectRoute()
    {
        $app = Application::create(__DIR__);
        $app->get("/foo/{id}", "bar", $route = "foo-route");
        $response = Response::redirectRoute($app, $route, ["id" => 1], 301, [
            "foo" => "bar"
        ]);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals("/foo/1", $response->getHeaderLine("location"));
        $this->assertEquals(301, $response->getStatusCode());
    }

    public function testJsonString()
    {
        $response = Response::jsonString($data = '{"foo": "bar"}', 404, [
            "foo" => "bar"
        ]);
        $this->assertInstanceOf(JsonStringResponse::class, $response);
        $this->assertEquals("bar", $response->getHeaderLine("foo"));
        $this->assertEquals("application/json", $response->getHeaderLine("content-type"));

        $this->assertEquals($response->getPayload(), $data);
        $this->assertEquals((string)$response->getBody(), $data);
        $this->assertEquals(404, $response->getStatusCode());
    }

}
