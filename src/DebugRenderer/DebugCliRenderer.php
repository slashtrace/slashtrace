<?php

namespace SlashTrace\DebugRenderer;

use SlashTrace\Formatter\StackFrameCallTextFormatter;
use SlashTrace\Formatter\StackTraceTagFormatter;
use SlashTrace\System\CLImateOutput;
use League\CLImate\CLImate;

class DebugCliRenderer extends DebugTextRenderer
{
    /** @var StackTraceTagFormatter */
    private $tagFormatter;

    /**
     * @return CLImateOutput
     */
    protected function initOutputReceiver()
    {
        $climate = new CLImate();

        $style = $climate->style;

        $style->addColor(StackTraceTagFormatter::TAG_TYPE, 31);
        $style->addColor(StackTraceTagFormatter::TAG_MESSAGE, 33);
        $style->addColor(StackTraceTagFormatter::TAG_CLASS, 96);
        $style->addColor(StackTraceTagFormatter::TAG_FUNCTION, 33);
        $style->addColor(StackTraceTagFormatter::TAG_ARGUMENT, 95);
        $style->addColor(StackTraceTagFormatter::TAG_FILE, 39);
        $style->addColor(StackTraceTagFormatter::TAG_LINE, 39);

        return new CLImateOutput($climate);
    }

    protected function formatType($type)
    {
        return $this->tag($type, [
            StackTraceTagFormatter::TAG_BOLD,
            StackTraceTagFormatter::TAG_TYPE
        ]);
    }

    protected function formatMessage($message)
    {
        return $this->tag($message, StackTraceTagFormatter::TAG_MESSAGE);
    }

    protected function formatFile($file)
    {
        return $this->tag($file, StackTraceTagFormatter::TAG_FILE);
    }

    protected function formatLine($line)
    {
        return $this->tag($line, StackTraceTagFormatter::TAG_LINE);
    }

    protected function renderBreadcrumbData(array $data)
    {
        return $this->tag(
            parent::renderBreadcrumbData($data),
            StackTraceTagFormatter::TAG_ARGUMENT
        );
    }

    private function tag($input, $tags)
    {
        $tagFormatter = $this->getTagFormatter();
        $return = $input;
        foreach ((array) $tags as $tag) {
            $return = $tagFormatter->format($return, $tag);
        }
        return $return;
    }

    /**
     * @return StackTraceTagFormatter
     */
    public function getTagFormatter()
    {
        if (is_null($this->tagFormatter)) {
            $this->tagFormatter = new StackTraceTagFormatter();
            $this->tagFormatter->setTags([
                StackTraceTagFormatter::TAG_TYPE,
                StackTraceTagFormatter::TAG_MESSAGE,
                StackTraceTagFormatter::TAG_CLASS,
                StackTraceTagFormatter::TAG_FUNCTION,
                StackTraceTagFormatter::TAG_ARGUMENT,
                StackTraceTagFormatter::TAG_FILE,
                StackTraceTagFormatter::TAG_LINE,
                StackTraceTagFormatter::TAG_BOLD
            ]);
        }
        return $this->tagFormatter;
    }

    protected function initStackFrameCallFormatter()
    {
        return new StackFrameCallTextFormatter($this->getTagFormatter());
    }
}