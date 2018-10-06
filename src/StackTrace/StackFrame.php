<?php

namespace SlashTrace\StackTrace;

use JsonSerializable;
use SlashTrace\Formatter\StackFrameCallTextFormatter;
use SlashTrace\Serializer\Serializer;

class StackFrame implements JsonSerializable
{
    const TYPE_METHOD = "->";
    const TYPE_STATIC = "::";
    const TYPE_FUNCTION = "";

    /** @var string */
    private $file;

    /** @var int */
    private $line;

    /** @var string */
    private $functionName;

    /** @var string */
    private $className;

    /** @var string */
    private $type = self::TYPE_FUNCTION;

    /** @var array */
    private $arguments = [];

    /** @var string[] */
    private $context = [];

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        // Force the use of the cross-platform directory separator
        $this->file = str_replace("\\", "/", $file);
    }

    public function getLine()
    {
        return $this->line;
    }

    public function setLine($line)
    {
        $this->line = $line;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function setClassName($className)
    {
        $this->className = $className;
    }

    public function getFunctionName()
    {
        return $this->functionName;
    }

    public function setFunctionName($functionName)
    {
        $this->functionName = $functionName;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function setContext(array $context)
    {
        $this->context = $context;
    }

    public function getRelativeFile($rootPath = null)
    {
        $path = $this->getFile();
        if (is_null($rootPath) || substr($path, 0, strlen($rootPath)) != $rootPath) {
            return $path;
        }
        return ltrim(substr($path, strlen($rootPath)), "/\\");
    }

    public function jsonSerialize()
    {
        $serializer = new Serializer();
        $callFormatter = new StackFrameCallTextFormatter();
        return array_filter([
            "at"        => $callFormatter->format($this),
            "file"      => $this->getFile(),
            "line"      => $this->getLine(),
            "arguments" => array_map(function ($argument) use ($serializer) {
                return $serializer->serialize($argument);
            }, $this->getArguments()),
        ]);
    }
}