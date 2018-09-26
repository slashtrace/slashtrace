<?php

namespace SlashTrace\Tests\Formatter;

use SlashTrace\Formatter\StackFrameCallFormatter;
use SlashTrace\StackTrace\StackFrame;
use SlashTrace\Tests\TestCase;

abstract class StackFrameCallFormatterTestCase extends TestCase
{
    /** @var StackFrameCallFormatter */
    protected $formatter;

    /** @var StackFrame */
    protected $frame;

    /**
     * @return StackFrameCallFormatter
     */
    abstract protected function createFormatter();

    protected function setUp()
    {
        parent::setUp();
        $this->formatter = $this->createFormatter();
        $this->frame = new StackFrame();
    }

    protected function assertFormat($expected)
    {
        $this->assertEquals(
            $expected,
            $this->formatter->format($this->frame)
        );
    }
}