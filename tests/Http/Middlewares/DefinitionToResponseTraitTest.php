<?php


namespace Atom\Framework\Test\Http\Middlewares;

use Atom\DI\Container;
use Atom\DI\Definition;
use Atom\DI\Definitions\CallFunction;
use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\MultipleBindingException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\Event\Exceptions\ListenerAlreadyAttachedToEvent;
use Atom\Framework\Application;
use Atom\Framework\Contracts\RendererContract;
use Atom\Framework\Contracts\RendererExtensionProvider;
use Atom\Framework\Http\Middlewares\DefinitionToResponseTrait;
use Atom\Framework\Http\Request;
use Atom\Framework\Http\RequestHandler;
use Atom\Framework\Http\Response;
use JsonSerializable;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;
use Throwable;

class DefinitionToResponseTraitTest extends TestCase
{
    /**
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws ListenerAlreadyAttachedToEvent
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testParametersArePassed()
    {
        $definition = new CallFunction(function ($foo) {
            return $foo . "bar";
        });
        $request = new Request();
        $app = Application::create(__DIR__);
        $handler = $app->requestHandler();
        $res = DefinitionToResponseTrait::definitionToResponse($definition, $request, $handler, [
            "foo" => "foo"
        ]);
        $this->assertInstanceOf(HtmlResponse::class, $res);
        $this->assertEquals("foobar", (string)$res->getBody());
    }

    /**
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws ListenerAlreadyAttachedToEvent
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testClassesAreMapped()
    {
        $response = Response::html("foobar");
        $definition = new CallFunction(function (ResponseInterface $response) {
            return $response;
        });
        $request = new Request();
        $app = Application::create(__DIR__);
        $handler = $app->requestHandler();
        $res = DefinitionToResponseTrait::definitionToResponse($definition, $request, $handler, [], [
            ResponseInterface::class => $response
        ]);
        $this->assertInstanceOf(HtmlResponse::class, $res);
    }

    /**
     * @throws CircularDependencyException
     * @throws ContainerException
     * @throws MultipleBindingException
     * @throws NotFoundException
     * @throws ListenerAlreadyAttachedToEvent
     * @throws ReflectionException
     * @throws Throwable
     */
    public function testCommonArgsArePassed()
    {
        $renderer = new class implements RendererContract {
            public function addGlobal(array $data)
            {
            }

            public function render(string $template, array $args = []): string
            {
                return $template;
            }

            public function addExtensions(RendererExtensionProvider $extensionProvider)
            {
            }
        };
        $definition = new CallFunction(function ($rend, $req, $hand, $ker, $res) {
            /** @var Container $c */
            $c = $hand->container();
            $c->bind("val", Definition::value([
                $hand,
                $ker
            ]));
            return $res->json([
                "data" => $rend->render("foo") . $req->getAttribute("bar")
            ]);
        });
        $request = (new Request())->withAttribute("bar", "bar");
        $app = Application::create(__DIR__);
        $handler = $app->requestHandler();
        $handler->container()->bind(RendererContract::class, $renderer);

        $res = DefinitionToResponseTrait::definitionToResponse($definition, $request, $handler);
        $this->assertInstanceOf(JsonResponse::class, $res);
        $this->assertEquals('{"data":"foobar"}', (string)$res->getBody());
        $this->assertEquals([
            $handler,
            $handler->getKernel()
        ], $handler->container()->get("val"));
    }

    public function testDataAreConvertedToResponse()
    {
        $definition = Definition::value("foo");
        $request = new Request();
        $handler = new RequestHandler(new Container());

        $res = DefinitionToResponseTrait::definitionToResponse(
            $definition,
            $request,
            $handler
        );
        $this->assertInstanceOf(HtmlResponse::class, $res);
        $this->assertEquals("foo", (string)$res->getBody());

        $res = DefinitionToResponseTrait::definitionToResponse(
            Definition::value(["foo" => "bar"]),
            $request,
            $handler
        );
        $this->assertInstanceOf(JsonResponse::class, $res);
        $this->assertEquals('{"foo":"bar"}', (string)$res->getBody());

        $class = new class implements JsonSerializable {

            public function jsonSerialize()
            {
                return ["foo" => "bar"];
            }
        };

        $res = DefinitionToResponseTrait::definitionToResponse(
            Definition::value($class),
            $request,
            $handler
        );
        $this->assertInstanceOf(JsonResponse::class, $res);
        $this->assertEquals('{"foo":"bar"}', (string)$res->getBody());

        $res = DefinitionToResponseTrait::definitionToResponse(
            Definition::value(1),
            $request,
            $handler
        );
        $this->assertInstanceOf(TextResponse::class, $res);
        $this->assertEquals('1', (string)$res->getBody());

        $res = DefinitionToResponseTrait::definitionToResponse(
            Definition::value($r = new Response()),
            $request,
            $handler
        );
        $this->assertEquals($r, $res);

        $this->expectException(\TypeError::class);
        $res = DefinitionToResponseTrait::definitionToResponse(
            Definition::value($r = new class {
            }),
            $request,
            $handler
        );
    }
}
