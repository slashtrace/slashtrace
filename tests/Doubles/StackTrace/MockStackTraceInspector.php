<?php

namespace SlashTrace\Tests\Doubles\StackTrace;

use SlashTrace\StackTrace\StackTraceInspector;

class MockStackTraceInspector extends StackTraceInspector
{

    private $stackTrace = [];

    public function fromException($exception)
    {
        return $this->stackTrace;
    }

    public function setStackTrace(array $stackTrace)
    {
        $this->stackTrace = $stackTrace;
    }

}