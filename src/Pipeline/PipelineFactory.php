<?php


namespace Atom\Framework\Pipeline;

use Atom\Framework\Contracts\PipelineProcessorContract;

class PipelineFactory
{
    protected array $pipes = [];
    /**
     * @var mixed
     */
    protected $data = null;

    protected ?PipelineProcessorContract $processor = null;

    /**
     * @param $data
     * @return PipelineFactory
     */
    public function pipe($data): PipelineFactory
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param array $pipes
     * @return $this
     */
    public function through(array $pipes): PipelineFactory
    {
        $this->pipes = $pipes;
        return $this;
    }

    public function add($pipe): PipelineFactory
    {
        $this->pipes[] = $pipe;
        return $this;
    }

    public function addPipes(array $pipes): PipelineFactory
    {
        $this->pipes = array_merge($this->pipes ?? [], $pipes);
        return $this;
    }

    public function via(
        PipelineProcessorContract $processor
    ): PipelineFactory
    {
        $this->processor = $processor;
        return $this;
    }

    /**
     * @return Pipeline
     * @throws NoDataException
     * @throws NoPipesException
     * @throws NoProcessorException
     */
    public function make(): Pipeline
    {
        if (is_null($this->data)) {
            throw new NoDataException("tried to make a pipeline without data");
        }
        if (is_null($this->processor)) {
            throw new NoProcessorException("tried to make a pipeline without a pipeline processor");
        }
        if (is_null($this->pipes) || empty($this->pipes)) {
            throw new NoPipesException("tried to make a pipeline without pipes");
        }
        return new Pipeline($this->data, $this->processor, $this->pipes);
    }

    public function run()
    {
        return $this->make()->run();
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getPipes(): array
    {
        return $this->pipes;
    }

    /**
     * @return PipelineProcessorContract
     */
    public function getProcessor(): PipelineProcessorContract
    {
        return $this->processor;
    }
}
