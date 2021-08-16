<?php


namespace Atom\Framework\Http\Middlewares;

use Atom\DI\Definition;
use Atom\DI\Definitions\AbstractDefinition;
use Atom\Framework\Contracts\RendererContract;
use Atom\Framework\Http\RequestHandler;
use Atom\Framework\Http\Response;
use Atom\Framework\Http\ResponseSender;
use Atom\Framework\Kernel;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

trait DefinitionToResponseTrait
{
    /**
     * @param AbstractDefinition $definition
     * @param ServerRequestInterface $request
     * @param RequestHandler $handler
     * @param array $args
     * @param array $mapping
     * @return ResponseInterface
     */
    public static function definitionToResponse(
        AbstractDefinition $definition,
        ServerRequestInterface $request,
        RequestHandler $handler,
        array $args = [],
        array $mapping = []
    ): ResponseInterface
    {
        $c = $handler->container();
        $definition
            ->withParameters($args)
            ->withClasses($mapping)
            ->withParameters([
                "rend" => Definition::get(RendererContract::class),
                "hand" => $handler,
                "req" => $request,
                "ker" => Definition::get(Kernel::class),
                "res" => Definition::get(ResponseSender::class)
            ]);
        $c->getResolutionStack()->clear();
        $response = $c->interpret($definition);
        $c->getResolutionStack()->clear();
        if (is_array($response) ||
            (is_object($response) && ($response instanceof JsonSerializable))) {
            return Response::json($response);
        }
        if (is_string($response)) {
            return Response::html($response);
        }
        if (is_scalar($response)) {
            return Response::text((string)$response);
        }
        return $response;
    }
}
