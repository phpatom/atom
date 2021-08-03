<?php


namespace Atom\Framework\Test\Http;

use Atom\Framework\Http\JsonStringResponse;
use Atom\Framework\Http\ResponseSender;
use Atom\Routing\Router;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\TextResponse;
use PHPUnit\Framework\TestCase;
use Throwable;

class ResponseSenderTest extends TestCase
{

    private function makeSender(): ResponseSender
    {
        $router = new Router();
        $router->get("/foo/{id}", "bar", "foo-route");
        return new ResponseSender($router);
    }

    public function testJsonResponse()
    {
        $response = $this->makeSender()->json($data = ["foo" => "bar"], 404, [
            "foo" => "bar"
        ], 0);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals("bar", $response->getHeaderLine("foo"));
        $this->assertEquals((string)$response->getBody(), json_encode($data));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testHtmlResponse()
    {
        $response = $this->makeSender()->html($data = "<p>foo</p>", 404, [
            "foo" => "bar"
        ]);
        $this->assertInstanceOf(HtmlResponse::class, $response);
        $this->assertEquals("bar", $response->getHeaderLine("foo"));
        $this->assertEquals((string)$response->getBody(), $data);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testTextResponse()
    {
        $response = $this->makeSender()->text($data = "foo", 404, [
            "foo" => "bar"
        ]);
        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertEquals("bar", $response->getHeaderLine("foo"));
        $this->assertEquals((string)$response->getBody(), $data);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testEmptyResponse()
    {
        $response = $this->makeSender()->empty(404, [
            "foo" => "bar"
        ]);
        $this->assertInstanceOf(EmptyResponse::class, $response);
        $this->assertEquals("bar", $response->getHeaderLine("foo"));
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testRedirectResponse()
    {
        $response = $this->makeSender()->redirect($to = "https://example.com", 301, [
            "foo" => "bar"
        ]);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals($to, $response->getHeaderLine("location"));
        $this->assertEquals(301, $response->getStatusCode());
    }

    /**
     * @throws Throwable
     */
    public function testRedirectRoute()
    {
        $response = $this->makeSender()->redirectRoute("foo-route", ["id" => 1], 301, [
            "foo" => "bar"
        ]);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals("/foo/1", $response->getHeaderLine("location"));
        $this->assertEquals(301, $response->getStatusCode());
    }

    public function testJsonString()
    {
        $response = $this->makeSender()->jsonString($data = '{"foo": "bar"}', 404, [
            "foo" => "bar"
        ]);
        $this->assertInstanceOf(JsonStringResponse::class, $response);
        $this->assertEquals("bar", $response->getHeaderLine("foo"));
        $this->assertEquals("application/json", $response->getHeaderLine("content-type"));

        $this->assertEquals($response->getPayload(), $data);
        $this->assertEquals((string)$response->getBody(), $data);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testSend()
    {
        $sender = $this->makeSender();

        $emptyRes = $sender->send(null, 201, ["foo" => "bar"]);
        $this->assertInstanceOf(EmptyResponse::class, $emptyRes);
        $this->assertEquals("bar", $emptyRes->getHeaderLine("foo"));
        $this->assertEquals(201, $emptyRes->getStatusCode());

        $jsonRes = $sender->send(["foo" => "bar"], 404, ["foo" => "bar"]);
        $this->assertInstanceOf(JsonResponse::class, $jsonRes);
        $this->assertEquals("bar", $jsonRes->getHeaderLine("foo"));
        $this->assertEquals(404, $jsonRes->getStatusCode());

        $data = new class implements \JsonSerializable {

            public function jsonSerialize(): array
            {
                return [
                    "foo" => "bar"
                ];
            }
        };
        $jsonRes = $sender->send($data, 404, ["foo" => "bar"]);
        $this->assertInstanceOf(JsonResponse::class, $jsonRes);
        $this->assertEquals('{"foo":"bar"}', (string)$jsonRes->getBody());
        $this->assertEquals("bar", $jsonRes->getHeaderLine("foo"));
        $this->assertEquals(404, $jsonRes->getStatusCode());

        $htmlRes = $sender->send($data = "<p>foo</p>", 404, ["foo" => "bar"]);
        $this->assertInstanceOf(HtmlResponse::class, $htmlRes);
        $this->assertEquals($data, (string)$htmlRes->getBody());
        $this->assertEquals("bar", $htmlRes->getHeaderLine("foo"));
        $this->assertEquals(404, $htmlRes->getStatusCode());
    }
}
