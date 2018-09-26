<?php

namespace SlashTrace\Tests\Formatter;

use SlashTrace\Formatter\StackFrameCallTextFormatter;
use SlashTrace\Formatter\StackTraceTagFormatter;
use SlashTrace\StackTrace\StackFrame;
use stdClass;

/**
 * @covers \SlashTrace\Formatter\StackFrameCallTextFormatter
 */
class StackFrameCallTextFormatterTest extends StackFrameCallFormatterTestCase
{
    /** @var StackTraceTagFormatter */
    private $tagFormatter;

    protected function createFormatter()
    {
        return new StackFrameCallTextFormatter($this->createTagFormatter());
    }

    private function createTagFormatter()
    {
        $this->tagFormatter = new StackTraceTagFormatter();
        return $this->tagFormatter;
    }

    public function testStackTraceFunctionCall()
    {
        $this->frame->setFunctionName("test");
        $this->assertFormat("test()");
    }

    public function testStackTraceClassCall()
    {
        $this->frame->setClassName("TestClass");
        $this->assertFormat("TestClass");
    }

    public function testStackFrameNonStaticMethodCall()
    {
        $this->frame->setFunctionName("test");
        $this->frame->setClassName("TestClass");
        $this->frame->setType(StackFrame::TYPE_METHOD);

        $this->assertFormat("TestClass->test()");
    }

    public function testStackFrameStaticMethodCall()
    {
        $this->frame->setFunctionName("test");
        $this->frame->setClassName("TestClass");
        $this->frame->setType(StackFrame::TYPE_STATIC);

        $this->assertFormat("TestClass::test()");
    }

    public function testStackFrameCallWithArguments()
    {
        $this->frame->setFunctionName("test");
        $this->frame->setArguments([
            "/var/www/index.php",
            new stdClass,
            [1, 2, 3]
        ]);

        $this->assertFormat('test("/var/www/index.php", Object[stdClass], array[3])');
    }

    public function testTagFormatterIsUsed()
    {
        $this->tagFormatter->setTags([
            StackTraceTagFormatter::TAG_CLASS => "class",
            StackTraceTagFormatter::TAG_FUNCTION => "function",
            StackTraceTagFormatter::TAG_ARGUMENT => "argument"
        ]);

        $this->frame->setClassName("TestNamespace\\TestClass");
        $this->frame->setType(StackFrame::TYPE_METHOD);
        $this->frame->setFunctionName("test");
        $this->frame->setArguments([1, 2]);

        $this->assertFormat("TestNamespace\\<class>TestClass</class>-><function>test</function>(<argument>1</argument>, <argument>2</argument>)");
    }
}