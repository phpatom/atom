<?php


namespace Atom\Framework\Test\Pipeline;

use Atom\Framework\Contracts\PipelineProcessorContract;
use Atom\Framework\Pipeline\PipelineFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PipelineFactoryTest extends TestCase
{

    public function testPipe()
    {
        $factory = new PipelineFactory();
        $factory->pipe("foo");
        $this->assertEquals("foo", $factory->getData());
    }

    public function testThrough()
    {
        $factory = new PipelineFactory();
        $factory->through($pipes = [
            "foo",
            "bar"
        ]);
        $this->assertEquals($pipes, $factory->getPipes());
    }

    public function testVia()
    {
        $processor = $this->getMockBuilder(PipelineProcessorContract::class)->getMock();
        $factory = new PipelineFactory();
        $factory->via($processor);
        $this->assertEquals($factory->getProcessor(), $processor);
    }

    public function testMakeFailedIfDataIsMissing()
    {
        $processor = $this->getMockBuilder(PipelineProcessorContract::class)->getMock();
        $factory = new PipelineFactory();
        $factory->via($processor)->through(["foo", "bar"]);
        $this->expectException(RuntimeException::class);
        $factory->make();
    }

    public function testMakeFailedIfProcessorIsMissing()
    {
        $factory = new PipelineFactory();
        $factory->pipe("baz")->through(["foo", "bar"]);
        $this->expectException(RuntimeException::class);
        $factory->make();
    }

    public function testMakeFailedIfPipesAreMissing()
    {
        $processor = $this->getMockBuilder(PipelineProcessorContract::class)->getMock();
        $factory = new PipelineFactory();
        $factory->pipe("baz")->via($processor);
        $this->expectException(RuntimeException::class);
        $factory->make();
    }

    public function testMakeFailedIfPipesAreEmpty()
    {
        $processor = $this->getMockBuilder(PipelineProcessorContract::class)->getMock();
        $factory = new PipelineFactory();
        $factory->pipe("baz")->via($processor)->through([]);
        $this->expectException(RuntimeException::class);
        $factory->make();
    }

    public function testRun()
    {
        $processor = $this->getMockBuilder(PipelineProcessorContract::class)->getMock();
        $processor->method("shouldStop")->willReturn(true);
        $processor->method("process")->willReturn(1);
        $factory = new PipelineFactory();
        $factory->pipe("baz")->via($processor)->through(["bar"]);
        $this->assertEquals(1, $factory->run());
    }

    public function testAdd()
    {
        $factory = new PipelineFactory();
        $factory->through(["bar", "baz"]);
        $factory->add("foo");
        $this->assertEquals(["bar", "baz", "foo"], $factory->getPipes());
    }

    public function testAddPipes()
    {
        $factory = new PipelineFactory();
        $factory->through(["bar", "baz"]);
        $factory->addPipes(["foo", "doe"]);
        $this->assertEquals(["bar", "baz", "foo", "doe"], $factory->getPipes());
    }
}
