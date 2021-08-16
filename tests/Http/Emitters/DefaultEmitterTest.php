<?php


namespace Atom\Framework\Test\Http\Emitters;

use Atom\Framework\Contracts\EmitterContract;
use Atom\Framework\Contracts\StreamEmitterContract;
use Atom\Framework\Http\Emitters\DefaultEmitter;
use Atom\Framework\Http\Response;
use PHPUnit\Framework\TestCase;

class DefaultEmitterTest extends TestCase
{
    public function testEmit()
    {
        $emitterMock = $this->getMockBuilder(EmitterContract::class)
            ->getMock();
        $streamEmitterMock = $this->getMockBuilder(StreamEmitterContract::class)
            ->getMock();
        $maxBufferLength = 512;
        $response = new Response();
        $downloadResponse = (new Response())
            ->withHeader("Content-Disposition", 'attachment; filename="filename.jpg"');
        $emitterMock->expects($this->once())->method("emit")->with($response);
        $streamEmitterMock->expects($this->once())
            ->method("emit")->with($downloadResponse);

        $emitter = new DefaultEmitter(
            $maxBufferLength,
            $emitterMock,
            $streamEmitterMock
        );
        $emitter->emit($response);
        $emitter->emit($downloadResponse);

        $this->assertEquals($emitter->getMaxBufferLength(), $maxBufferLength);
    }
}
