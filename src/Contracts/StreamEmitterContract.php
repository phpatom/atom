<?php


namespace Atom\Framework\Contracts;

use Psr\Http\Message\ResponseInterface;

interface StreamEmitterContract extends EmitterContract
{
    public function emit(ResponseInterface $response, int $maxBufferLength = 8192): void;
}
