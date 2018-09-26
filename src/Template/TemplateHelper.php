<?php

namespace SlashTrace\Template;

use SlashTrace\Formatter\StackFrameCallFormatter;
use SlashTrace\Formatter\StackFrameCallHtmlFormatter;
use SlashTrace\Formatter\VarDumper;
use SlashTrace\StackTrace\StackFrame;

class TemplateHelper
{
    /** @var VarDumper */
    private $varDumper;

    /** @var StackFrameCallFormatter */
    private $stackFrameCallFormatter;

    /**
     * @param StackFrame $frame
     * @return string
     */
    public function formatStackFrameCall(StackFrame $frame)
    {
        return $this->getStackFrameCallFormatter()->format($frame);
    }

    public function formatStackFrameContext(StackFrame $frame)
    {
        $lines = $frame->getContext();
        if (!count($lines)) {
            return "";
        }

        $attributes = [];

        $firstLine = array_keys($lines)[0];
        if ($firstLine > 1) {
            $attributes["data-start"] = $firstLine;
        }

        $line = $frame->getLine();
        if (!is_null($line)) {
            $attributes["data-line"] = $line;
        }

        $attributes = $this->formatHMLAttributes($attributes);

        $return = $attributes ? "<pre $attributes>" : "<pre>";
        $return .= implode("\n", $this->escapeCodeLines($lines));
        $return .= "</pre>";

        return $return;
    }

    /**
     * @param array $attributes
     * @return string
     */
    private function formatHMLAttributes(array $attributes)
    {
        $return = [];
        foreach ($attributes as $key => $value) {
            $return[] = "$key=\"$value\"";
        }
        return implode(" ", $return);
    }

    /**
     * @param array $lines
     * @return array
     */
    private function escapeCodeLines(array $lines)
    {
        $return = [];
        foreach ($lines as $line) {
            if (!strlen($line)) {
                $line = " ";
            }
            $return[] = htmlentities($line, ENT_QUOTES, "UTF-8");
        }
        return $return;
    }

    public function dump($argument)
    {
        return $this->getVarDumper()->dump($argument);
    }

    public function getVarDumper()
    {
        if (is_null($this->varDumper)) {
            $this->varDumper = new VarDumper();
        }
        return $this->varDumper;
    }

    public function setVarDumper(VarDumper $dumper)
    {
        $this->varDumper = $dumper;
    }

    /**
     * @return StackFrameCallFormatter
     */
    private function getStackFrameCallFormatter()
    {
        if (is_null($this->stackFrameCallFormatter)) {
            $this->stackFrameCallFormatter = new StackFrameCallHtmlFormatter();
        }
        return $this->stackFrameCallFormatter;
    }
}