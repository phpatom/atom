<?php


namespace Atom\Framework\Http\Emitters;

use Atom\Framework\Contracts\EmitterContract;
use Atom\Framework\Contracts\StreamEmitterContract;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractEmitter implements EmitterContract
{
    /**
     * @var EmitterContract
     */
    private EmitterContract $emitter;
    /**
     * @var StreamEmitterContract
     */
    private StreamEmitterContract $streamEmitter;

    public function __construct(
        EmitterContract $emitter,
        StreamEmitterContract $streamEmitter
    )
    {
        $this->emitter = $emitter;
        $this->streamEmitter = $streamEmitter;
    }

    /**
     * @return StreamEmitterContract
     */
    public function getStreamEmitter(): StreamEmitterContract
    {
        return $this->streamEmitter;
    }

    /**
     * @return EmitterContract
     */
    public function getEmitter(): EmitterContract
    {
        return $this->emitter;
    }

    public function isDownload(ResponseInterface $response): bool
    {
        return $response->hasHeader('Content-Disposition') || $response->hasHeader('Content-Range');
    }
}
