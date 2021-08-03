<?php


namespace Atom\Framework\Contracts;

use Atom\Framework\Pipeline\Pipeline;

interface PipelineProcessorContract
{
    public function process($data, $handler, Pipeline $pipeline);

    public function shouldStop($result);
}
