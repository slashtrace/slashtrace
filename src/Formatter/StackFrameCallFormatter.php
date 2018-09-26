<?php

namespace SlashTrace\Formatter;

use SlashTrace\Serializer\HumanSerializer;
use SlashTrace\StackTrace\StackFrame;

abstract class StackFrameCallFormatter
{
    /** @var HumanSerializer */
    private $humanSerializer;

    const NS = "\\";

    abstract protected function formatClass($class);

    abstract protected function formatFunction($function);

    abstract public function formatArgument($argument);

    public function format(StackFrame $frame)
    {
        $namespace = [];
        $tags = [];

        $className = $frame->getClassName();
        $functionName = $frame->getFunctionName();

        if ($className) {
            $namespace = explode(StackFrameCallFormatter::NS, $className);
            $tags[] = $this->formatClass(array_pop($namespace));
        }

        if ($functionName) {
            $namespace = array_merge($namespace, explode(StackFrameCallFormatter::NS, $functionName));
            if ($className) {
                $tags[] = $frame->getType();
            }
            $tags[] = $this->formatFunction(array_pop($namespace));
            $tags[] = "(" . $this->formatArguments($frame->getArguments()) . ")";
        }

        $namespace[] = implode("", $tags);

        return implode(StackFrameCallFormatter::NS, $namespace);
    }

    protected function formatArguments(array $arguments)
    {
        $return = [];
        foreach ($arguments as $argument) {
            $return[] = $this->formatArgument($argument);
        }
        return implode(", ", $return);
    }

    protected function serialize($argument)
    {
        return $this->getHumanSerializer()->serialize($argument);
    }

    /**
     * @return HumanSerializer
     */
    private function getHumanSerializer()
    {
        if (is_null($this->humanSerializer)) {
            $this->humanSerializer = new HumanSerializer();
        }
        return $this->humanSerializer;
    }
}