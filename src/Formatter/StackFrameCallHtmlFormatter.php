<?php

namespace SlashTrace\Formatter;

class StackFrameCallHtmlFormatter extends StackFrameCallFormatter
{
    protected function formatClass($class)
    {
        return '<span class="class">' . $class . '</span>';
    }

    protected function formatFunction($function)
    {
        return '<span class="function">' . $function . '</span>';
    }

    public function formatArgument($argument)
    {
        return '<span class="argument ' . $this->getArgumentClass($argument) . '">' . $this->serialize($argument) . "</span>";
    }

    private function getArgumentClass($argument)
    {
        if (is_null($argument)) {
            return "argument-null";
        }
        if (is_integer($argument)) {
            return "argument-integer";
        }
        if (is_float($argument)) {
            return "argument-float";
        }
        if ($argument === true) {
            return "argument-boolean-true";
        }
        if ($argument === false) {
            return "argument-boolean-false";
        }
        if (is_object($argument)) {
            return "argument-object";
        }
        if (is_array($argument)) {
            return "argument-array";
        }
        if (is_string($argument) && !is_callable($argument)) {
            return "argument-string";
        }
        return "argument-mixed";
    }
}