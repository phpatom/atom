<?php


namespace Atom\Framework\Http\Emitters;

use Atom\Framework\Contracts\EmitterContract;
use Psr\Http\Message\ResponseInterface;

class SapiEmitter implements EmitterContract
{

    public function emit(ResponseInterface $response): void
    {
        $emitter = new \Narrowspark\HttpEmitter\SapiEmitter();
        $emitter->emit($response);
    }
}
