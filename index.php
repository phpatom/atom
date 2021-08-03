<?php

use Atom\DI\Exceptions\CircularDependencyException;
use Atom\DI\Exceptions\ContainerException;
use Atom\DI\Exceptions\MultipleBindingException;
use Atom\DI\Exceptions\NotFoundException;
use Atom\Event\Exceptions\ListenerAlreadyAttachedToEvent;
use Atom\Framework\Http\Middlewares\Middleware;
use Atom\Framework\Http\RequestHandler;
use Atom\Framework\Http\Response;

require_once "vendor/autoload.php";

/**
 * @throws CircularDependencyException
 * @throws ContainerException
 * @throws ListenerAlreadyAttachedToEvent
 * @throws NotFoundException
 * @throws ReflectionException
 * @throws Throwable
 */
function run()
{
    ini_set("display_errors","1");
    $app = atom(__DIR__);
    $app->post("/hello/{name}", function (string $name) {
        return Response::text("hello, $name");
    });
    $app->withRouting()->run();
}
run();
