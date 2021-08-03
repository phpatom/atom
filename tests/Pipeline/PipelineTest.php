<?php


namespace Atom\Framework\Test\Pipeline;

use Atom\Framework\Contracts\PipelineProcessorContract;
use Atom\Framework\Pipeline\Pipeline;
use Atom\Framework\Pipeline\PipelineFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PipelineTest extends TestCase
{
    public function getProcessorMock()
    {
        return $this
            ->getMockBuilder(PipelineProcessorContract::class)
            ->getMock();
    }

    public function testLength()
    {
        $factory = new PipelineFactory();
        $factory->pipe(1)
            ->through(["bar", "baz"])
            ->via($this->getProcessorMock());
        $pipeline = $factory->make();
        $this
            ->assertEquals(2, $pipeline->length());
    }

    public function testCurrent()
    {
        $factory = new PipelineFactory();
        $factory->pipe(1)
            ->through(["bar", "baz"])
            ->via($this->getProcessorMock());
        $pipeline = $factory->make();
        $this
            ->assertEquals(1, $pipeline->current());
    }

    public function testCompleted()
    {
        $processor = $this->getProcessorMock();
        $processor->method("shouldStop")->willReturn(true);
        $processor->method("process")->willReturnArgument(2);
        $factory = new PipelineFactory();

        $factory->pipe(1)
            ->through(["bar", "baz"])
            ->via($processor);

        $pipeline = $factory->make();
        $this->assertFalse($pipeline->completed());
        $pipeline->run();
        $this->assertTrue($pipeline->completed());
    }

    public function testRun()
    {
        $processor = $this->getProcessorMock();
        $processor->method("shouldStop")->willReturn(false);
        $processor->method("process")->willReturnArgument(1);

        $factory = new PipelineFactory();
        $factory->pipe(1)
            ->through(["bar", "baz"])
            ->via($processor);

        $pipeline = $factory->make();
        $pipeline->run();
        $this->assertEquals(
            "baz",
            $pipeline->current()
        );
    }

    public function testSend()
    {
        $processor = $this->getProcessorMock();
        $processor->method("shouldStop")->willReturn(true);
        $processor->method("process")->willReturnArgument(0);

        $pipeline = Pipeline::send(1)
            ->through(["bar", "baz"])
            ->via($processor)
            ->make();

        $pipeline->run();
        $this->assertEquals(
            1,
            $pipeline->current()
        );
    }

    public function testWith()
    {
        $pipeline = Pipeline::send(1)
            ->through($pipes = ["foo", "bar"])
            ->via($this->getProcessorMock())
            ->make();
        $pipeline2 = $pipeline->with(2);
        $this->assertNotEquals($pipeline->current(), $pipeline2->current());
        $this->assertEquals(2, $pipeline2->current());
    }

    public function testWithAddedPipes()
    {
        $pipeline = Pipeline::send(1)
            ->through($pipes = ["foo", "bar"])
            ->via($this->getProcessorMock())
            ->make();
        $this->assertEquals($pipes, $pipeline->getPipes());
        $pipeline2 = $pipeline->withAddedPipes([
            "baz", "foobar"
        ]);
        $this->assertEquals(["baz", "foobar", "foo", "bar"], $pipeline2->getPipes());
        $this->assertNotEquals($pipeline->getPipes(), $pipeline2->getPipes());
        $pipeline3 = $pipeline2->withAddedPipes(["jhon", "doe"], 2);
        $this->assertEquals(
            ["baz", "foobar", "jhon", "doe", "foo", "bar"],
            $pipeline3->getPipes()
        );
    }

    public function testNextIndex()
    {
        $pipeline = Pipeline::send(1)
            ->through($pipes = ["foo", "bar", "baz"])
            ->via($this->getProcessorMock())
            ->make();
        $this->assertEquals(0, $pipeline->getNextIndex());
        $pipeline->next();
        $this->assertEquals(1, $pipeline->getNextIndex());
        $pipeline->next();
        $this->assertEquals(2, $pipeline->getNextIndex());
        $pipeline->next();
        $this->assertEquals(3, $pipeline->getNextIndex());
        $pipeline->next();
        $pipeline->next();
        $this->assertEquals(3, $pipeline->getNextIndex());
    }

    public function testGetIndex()
    {
        $pipeline = Pipeline::send(1)
            ->through($pipes = ["foo", "bar", "baz"])
            ->via($this->getProcessorMock())
            ->make();
        $this->assertEquals(-1, $pipeline->getCurrentIndex());
        $pipeline->next();
        $this->assertEquals(0, $pipeline->getCurrentIndex());
        $this->assertEquals(1, $pipeline->getNextIndex());
    }

    public function testGetProcessor()
    {
        $pipeline = Pipeline::send(1)
            ->through($pipes = ["foo", "bar", "baz"])
            ->via($processor = $this->getProcessorMock())
            ->make();
        $this->assertEquals($processor, $pipeline->getProcessor());
    }

    public function testItThrowIfTheStartIndexIsNotValid()
    {
        $this->expectException(InvalidArgumentException::class);
        new Pipeline(
            "foo",
            $this->getProcessorMock(),
            [],
            -2
        );
    }
}
