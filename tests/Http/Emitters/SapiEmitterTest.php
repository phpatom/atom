<?php


namespace Atom\Framework\Test\Http\Emitters;

use Atom\Framework\Http\Emitters\SapiStreamEmitter;
use Atom\Framework\Http\Response;
use PHPUnit\Framework\TestCase;

class SapiEmitterTest extends TestCase
{
    public function testEmit()
    {
        $response = (Response::text("foo"));
        ob_start();
        (new SapiStreamEmitter())->emit($response);
        $body = ob_get_clean();
        $this->assertEquals($body, "foo");
    }
}
