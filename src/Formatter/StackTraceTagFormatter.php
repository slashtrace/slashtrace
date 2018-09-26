<?php

namespace SlashTrace\Formatter;

class StackTraceTagFormatter
{
    const TAG_TYPE = "type";
    const TAG_MESSAGE = "message";
    const TAG_CLASS = "class";
    const TAG_FUNCTION = "function";
    const TAG_ARGUMENT = "argument";
    const TAG_FILE = "file";
    const TAG_LINE = "line";
    const TAG_BOLD = "bold";

    private $tags = [];

    public function format($string, $tag)
    {
        if (!isset($this->tags[$tag])) {
            return $string;
        }
        $tag = $this->tags[$tag];
        return "<$tag>$string</$tag>";
    }

    public function setTags(array $tags)
    {
        foreach ($tags as $key => $value) {
            if (is_int($key)) {
                $key = $value;
            }
            $this->tags[$key] = $value;
        }
    }
}