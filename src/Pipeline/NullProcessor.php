<?php


namespace Atom\Framework\Pipeline;

use Atom\Framework\Contracts\PipelineProcessorContract;

class NullProcessor implements PipelineProcessorContract
{

    public function process($data, $handler, Pipeline $pipeline)
    {
        return null;
    }

    public function shouldStop($result): bool
    {
        return true;
    }
}
