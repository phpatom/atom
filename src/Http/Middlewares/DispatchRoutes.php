<?php


namespace Atom\Framework\Http\Middlewares;

use Atom\Framework\Http\RequestHandler;
use Atom\Routing\Contracts\RouterContract;
use Atom\Routing\MatchedRoute;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DispatchRoutes extends AbstractMiddleware
{
    /**
     * @var RouterContract
     */
    private RouterContract $router;

    public function __construct(RouterContract $router)
    {
        $this->router = $router;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandler $handler
     * @return ResponseInterface
     * @throws Exception
     */
    public function run(ServerRequestInterface $request, RequestHandler $handler): ResponseInterface
    {
        $request = $this->router->dispatch($request);
        $matchedRoute = MatchedRoute::of($request);
        $routeHandler = $matchedRoute->getRoute()->getHandler();
        $routeGroup = $matchedRoute->getRoute()->getRouteGroup();
        $groupHandler = $routeGroup != null ? $routeGroup->getHandler() : null;
        $routeHandlers = [$groupHandler, $routeHandler];
        $middlewares = [];
        foreach ($routeHandlers as $middleware) {
            if ($middleware != null) {
                $middlewares[] = $middleware;
            }
        }
        return $handler
            ->withNext($middlewares)
            ->handle($request);
    }
}
