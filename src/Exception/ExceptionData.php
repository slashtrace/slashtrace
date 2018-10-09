<?php

namespace SlashTrace\Exception;

use JsonSerializable;
use SlashTrace\StackTrace\StackFrame;

class ExceptionData implements JsonSerializable
{
    /** @var string */
    private $message;

    /** @var string */
    private $type;

    /** @var StackFrame[] */
    private $stackTrace = [];

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return StackFrame[]
     */
    public function getStackTrace()
    {
        return $this->stackTrace;
    }

    /**
     * @param StackFrame[] $stackTrace
     */
    public function setStackTrace(array $stackTrace)
    {
        $this->stackTrace = $stackTrace;
    }

    public function jsonSerialize()
    {
        return array_filter([
            "type"       => $this->getType(),
            "message"    => $this->getMessage(),
            "stacktrace" => $this->getStackTrace(),
        ]);
    }
}