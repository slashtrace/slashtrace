<?php

namespace SlashTrace\Tests\Doubles\StackTrace;

use SlashTrace\StackTrace\StackTraceInspector;

class MaxDepthStackTraceInspector extends StackTraceInspector
{

    private $maxDepth = 10;

    public function __construct($maxDepth)
    {
        $this->maxDepth = $maxDepth;
    }

    public function fromException($exception)
    {
        return array_slice(parent::fromException($exception), 0, $this->maxDepth);
    }


}