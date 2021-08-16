<?php


namespace Atom\Framework\Http\Emitters;

use Atom\Framework\Contracts\EmitterContract;
use Atom\Framework\Contracts\StreamEmitterContract;
use Psr\Http\Message\ResponseInterface;

class DefaultEmitter extends AbstractEmitter
{
    private int $maxBufferLength;

    public function __construct(
        int $maxBufferLength = 8192,
        ?EmitterContract $emitter = null,
        ?StreamEmitterContract $streamEmitter = null,
    ) {
        parent::__construct(
            $emitter ?? new SapiEmitter(),
            $streamEmitter ?? new SapiStreamEmitter()
        );
        $this->maxBufferLength = $maxBufferLength;
    }

    public function emit(ResponseInterface $response): void
    {
        if ($this->isDownload($response)) {
            $this->getStreamEmitter()->emit(
                $response,
                $this->maxBufferLength
            );
        } else {
            $this->getEmitter()->emit($response);
        }
    }

    /**
     * @return int
     */
    public function getMaxBufferLength(): int
    {
        return $this->maxBufferLength;
    }
}
