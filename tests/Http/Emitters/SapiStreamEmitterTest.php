<?php

namespace Atom\Framework\Test\Http\Emitters {


    use Atom\Framework\Http\Emitters\SapiEmitter;
    use Atom\Framework\Http\Response;
    use PHPUnit\Framework\TestCase;

    class SapiStreamEmitterTest extends TestCase
    {
        public function testEmit()
        {
            $response = (Response::text("foo"));
            ob_start();
            (new SapiEmitter())->emit($response);
            $body = ob_get_clean();
            $this->assertEquals($body, "foo");
        }
    }
}

namespace Narrowspark\HttpEmitter {
    function headers_sent(): bool
    {
        return false;
    }

    function header()
    {
        return null;
    }
}