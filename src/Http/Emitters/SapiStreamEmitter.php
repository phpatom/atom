<?php


namespace Atom\Framework\Http\Emitters;

use Atom\Framework\Contracts\StreamEmitterContract;
use Narrowspark\HttpEmitter\SapiStreamEmitter as Emitter;
use Psr\Http\Message\ResponseInterface;

class SapiStreamEmitter implements StreamEmitterContract
{

    public function emit(
        ResponseInterface $response,
        int $maxBufferLength = 8192
    ): void {
        $emitter = new Emitter();
        $emitter->setMaxBufferLength($maxBufferLength)->emit($response);
    }
}
