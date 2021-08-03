<?php


namespace Atom\Framework\Pipeline;

use Atom\Framework\Contracts\PipelineProcessorContract;
use InvalidArgumentException;

class Pipeline
{
    private array $pipes;
    private int $nextIndex;
    private int $length;
    private int $currentIndex;
    /**
     * @var mixed|null
     */
    protected $current;
    /**
     * @var PipelineProcessorContract
     */
    private PipelineProcessorContract $processor;

    public function __construct(
        $data,
        PipelineProcessorContract $processor,
        array $pipes = [],
        int $currentIndex = -1,
        ?int $nexIndex = null
    )
    {
        $this->processor = $processor;
        $this->pipes = $pipes;
        if (($currentIndex + 1) < 0) {
            throw new InvalidArgumentException("the index should be greater than 0");
        }
        $this->nextIndex = $nexIndex ?? ($currentIndex + 1);
        $this->currentIndex = $currentIndex;
        $this->current = $data;
        $this->length = count($this->pipes);
    }

    public function length(): int
    {
        return $this->length;
    }

    /**
     * @return mixed|null
     */
    public function next()
    {
        $this->currentIndex = $this->nextIndex;
        $handler = $this->getPipe($this->currentIndex);
        if (is_null($handler)) {
            return null;
        }
        $this->nextIndex = $this->currentIndex + 1;
        $this->current = $this->processor->process($this->current, $handler, $this);
        if ($this->processor->shouldStop($this->current) ||
            ($this->nextIndex >= $this->length())
        ) {
            $this->nextIndex = $this->length;
            return $this->current;
        }
        return $this->current;
    }

    public function current()
    {
        return $this->current;
    }

    public function completed(): bool
    {
        return $this->currentIndex > ($this->length - 1);
    }

    public function run()
    {
        while (!$this->completed()) {
            $this->next();
        }
        return $this->current();
    }

    protected function getPipe(int $index)
    {
        return $this->pipes[$index] ?? null;
    }

    public static function send($data): PipelineFactory
    {
        return (new PipelineFactory())->pipe($data);
    }

    public function with($data): Pipeline
    {
        return new Pipeline(
            $data,
            $this->processor,
            $this->pipes,
            $this->currentIndex,
            $this->nextIndex
        );
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

    public function withAddedPipes(array $pipes, int $index = 0): Pipeline
    {
        $begin = array_slice($this->pipes, 0, $index);
        $begin = array_merge($begin, $pipes);
        $end = array_slice($this->pipes, $index);
        return new Pipeline(
            $this->current,
            $this->processor,
            array_merge($begin, $end),
            $this->currentIndex,
            $this->nextIndex
        );
    }

    /**
     * @return int
     */
    public function getNextIndex(): int
    {
        return $this->nextIndex;
    }

    /**
     * @return int
     */
    public function getCurrentIndex(): int
    {
        return $this->currentIndex;
    }
}
