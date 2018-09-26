<?php

namespace SlashTrace\Exception;

use SlashTrace\StackTrace\StackFrame;
use SlashTrace\StackTrace\StackTraceInspector;
use Exception;

class ExceptionInspector
{
    /** @var StackTraceInspector */
    private $stackTraceInspector;

    /**
     * @param Exception $exception
     * @return ExceptionData
     */
    public function inspect($exception)
    {
        $exceptionData = new ExceptionData();

        $exceptionData->setMessage($exception->getMessage());
        $exceptionData->setType(get_class($exception));
        $exceptionData->setStackTrace($this->getStackTrace($exception));

        return $exceptionData;
    }

    /**
     * @param Exception $exception
     * @return StackFrame[]
     */
    private function getStackTrace($exception)
    {
        return $this->getStackTraceInspector()->fromException($exception);
    }

    /**
     * @return StackTraceInspector
     */
    public function getStackTraceInspector()
    {
        if (is_null($this->stackTraceInspector)) {
            $this->stackTraceInspector = new StackTraceInspector();
        }
        return $this->stackTraceInspector;
    }

    public function setStackTraceInspector(StackTraceInspector $inspector)
    {
        $this->stackTraceInspector = $inspector;
    }
}