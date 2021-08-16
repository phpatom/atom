<?php


namespace Atom\Framework\Test\Http\Emitters;

use Atom\Framework\Http\Emitters\AbstractEmitter;
use Atom\Framework\Http\Emitters\SapiEmitter;
use Atom\Framework\Http\Emitters\SapiStreamEmitter;
use Atom\Framework\Http\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class AbstractEmitterTest extends TestCase
{
    private function makeEmitter(
        ?SapiEmitter $sapiEmitter = null,
        ?SapiStreamEmitter $sapiStreamEmitter = null
    ): AbstractEmitter {
        return new class(
            $sapiEmitter = $sapiEmitter ?? new SapiEmitter(),
            $sapiStreamEmitter = $sapiStreamEmitter ?? new SapiStreamEmitter()
        ) extends AbstractEmitter {

            public function emit(ResponseInterface $response): void
            {
            }
        };
    }

    public function testGetters()
    {
        $emitter = $this->makeEmitter(
            $sapiEmitter = new SapiEmitter(),
            $sapiStreamEmitter = new SapiStreamEmitter()
        );
        $this->assertEquals(
            $sapiEmitter,
            $emitter->getEmitter()
        );
        $this->assertEquals(
            $sapiStreamEmitter,
            $emitter->getStreamEmitter()
        );
    }

    public function testIsDownload()
    {
        $emitter = $this->makeEmitter();
        $this->assertFalse($emitter->isDownload(new Response()));
        $this->assertTrue($emitter->isDownload(
            (new Response())
                ->withHeader("Content-Disposition", 'attachment; filename="filename.jpg"')
        ));
        $this->assertTrue($emitter->isDownload(
            (new Response())
                ->withHeader("Content-Range", 'bytes 200-1000/67589')
        ));
    }
}
