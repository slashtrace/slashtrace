<?php

namespace SlashTrace\Tests\Doubles\StackTrace;

use SlashTrace\StackTrace\StackFrameContextExtractor;

class MockStackFrameContextExtractor extends StackFrameContextExtractor
{

    private $contextCalls = [];
    private $contextResult = [];

    public function getContext($file, $line, $contextLines = 5)
    {
        $this->contextCalls[] = [$file, $line];
        return $this->contextResult;
    }

    public function setContextResult(array $context)
    {
        $this->contextResult = $context;
    }

    public function getContextCalls()
    {
        return $this->contextCalls;
    }

    public function getContextResult()
    {
        return $this->contextResult;
    }

}