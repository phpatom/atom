<?php


namespace Atom\Framework\Test\Http;

use Atom\Framework\Http\Request;
use Atom\Routing\MatchedRoute;
use Atom\Routing\Route;
use Atom\Routing\Router;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testIncoming()
    {
        $_POST = ["foo" => "bar"];
        $request = Request::incoming();
        $this->assertEquals(["foo" => "bar"], $request->getParsedBody());
        $_POST = [];
    }

    public function testFrom()
    {
        $request = (new Request(["foo" => "bar"]))
            ->withAttribute("foo", "bar")
            ->withParsedBody($body = ["foo" => "bar"])
            ->withQueryParams($params = ["foo" => "bar"])
            ->withMethod("POST")
            ->withAddedHeader("X-FOO", "BAR")
            ->withUri($uri = new Uri("https://foo.com/bar"));
        $copy = Request::from($request);
        $this->assertEquals($body, $copy->getParsedBody());
        $this->assertEquals("bar", $copy->getAttribute("foo"));
        $this->assertEquals($params, $copy->getQueryParams());
        $this->assertTrue($copy->isMethod("post"));
        $this->assertEquals(["BAR"], $copy->getHeader("X-FOO"));
        $this->assertEquals($uri, $copy->getUri());
    }

    public function testRoute()
    {
        $request = new Request();
        $this->assertNull($request->route());
        /**
         * @var Request $request
         */
        $request = $request->withAttribute(
            Router::MATCHED_ROUTE_ATTRIBUTE_KEY,
            new MatchedRoute(
                new Route(["POST"], "/foo", "foo", "name"),
                ["foo"],
                "POST",
                "/foo"
            )
        );
        $route = $request->route();
        $this->assertNotNull($route);
        $this->assertTrue($route->isOfMethod("POST"));
        $this->assertTrue($route->isNamed("name"));
    }

    public function testIsJson()
    {
        $request = new Request();
        $this->assertFalse($request->isJson());

        $request = $request->withHeader("content-type", "application/json");
        $this->assertTrue($request->isJson());
        $request = $request->withHeader("content-type", "application/xml+json");
        $this->assertTrue($request->isJson());
        $request = $request->withHeader("content-type", "application/xml");
        $this->assertFalse($request->isJson());
    }

    public function testExpectJson()
    {
        $request = new Request();
        $request = $request
            ->withHeader("X-Requested-With", "XMLHttpRequest")
            ->withHeader("Accept", "*");
        $this->assertTrue($request->expectsJson());
        $request = $request->withHeader("X-PJAX", "1");
        $this->assertFalse($request->expectsJson());
        $request = new Request();
        $request = $request
            ->withHeader("Accept", "application/json");
        $this->assertTrue($request->expectsJson());
    }

    public function testIsAjax()
    {
        $request = new Request();
        $this->assertFalse($request->isAjax());
        $request = $request
            ->withHeader("X-Requested-With", "XMLHttpRequest");
        $this->assertTrue($request->isAjax());
    }

    public function testIsPJax()
    {
        $request = (new Request())->withHeader("X-PJAX", "1");
        $this->assertTrue($request->isPjax());
    }

    public function testWantJson()
    {
        $request = new Request();
        $this->assertFalse($request->wantsJson());
        $request = $request->withHeader("Accept", "text/html, application/json");
        $this->assertTrue($request->wantsJson());
        $request = (new Request())->withHeader("Accept", "text/html");
        $this->assertFalse($request->wantsJson());
    }

    public function testAccepts()
    {
        $request = new Request();
        $this->assertTrue($request->accepts("application/json"));
        $request = $request->withHeader("Accept", "text/html");
        $this->assertFalse($request->accepts("application/json"));
        $this->assertTrue($request->accepts("text/html"));

        $request = (new Request())->withHeader("Accept", ["text/html, application/json"]);
        $this->assertTrue($request->accepts("application/json"));
        $this->assertTrue($request->accepts("text/html"));

        $request = (new Request())->withHeader("Accept", "text/html, */*");
        $this->assertTrue($request->accepts("application/json"));
    }

    public function testAcceptJson()
    {
        $request = (new Request());
        $this->assertTrue($request->acceptsJson());
        $request = $request->withHeader("Accept", "text/html");
        $this->assertFalse($request->acceptsJson());
        $request = (new Request())->withHeader("Accept", "application/json");
        $this->assertTrue($request->acceptsJson());
    }

    public function testAcceptHtml()
    {
        $request = (new Request());
        $this->assertTrue($request->acceptsHtml());
        $request = $request->withHeader("Accept", "application/json");
        $this->assertFalse($request->acceptsHtml());
        $request = (new Request())->withHeader("Accept", "text/html");
        $this->assertTrue($request->acceptsHtml());
    }
}
