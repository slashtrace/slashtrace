<?php

namespace SlashTrace\Tests\Formatter;

use SlashTrace\Formatter\StackFrameCallHtmlFormatter;
use SlashTrace\StackTrace\StackFrame;
use SlashTrace\Template\TemplateHelper;
use stdClass;

/**
 * @covers \SlashTrace\Formatter\StackFrameCallHtmlFormatter
 */
class StackFrameCallHtmlFormatterTest extends StackFrameCallFormatterTestCase
{
    protected function createFormatter()
    {
        return new StackFrameCallHtmlFormatter();
    }

    private function assertArguments($expected, array $arguments)
    {
        $this->frame->setFunctionName("test");
        $this->frame->setArguments($arguments);
        $this->assertFormat('<span class="function">test</span>(' . $expected . ')');
    }


    public function testClassWithoutFunction()
    {
        $this->frame->setClassName("Test\\Namespace\\TestClass");
        $this->assertFormat('Test\\Namespace\\<span class="class">TestClass</span>');
    }

    public function testFunctionWithoutClass()
    {
        $this->frame->setFunctionName("Test\\Namespace\\do_something");
        $this->assertFormat('Test\\Namespace\\<span class="function">do_something</span>()');
    }

    public function testGlobalFunctionCall()
    {
        $this->frame->setFunctionName("include");
        $this->assertFormat('<span class="function">include</span>()');
    }

    public function testClassWithFunctionCall()
    {
        $this->frame->setClassName("Test\\Namespace\\TestClass");
        $this->frame->setFunctionName("do_something");
        $this->frame->setType(StackFrame::TYPE_METHOD);

        $this->assertFormat('Test\\Namespace\\<span class="class">TestClass</span>-><span class="function">do_something</span>()');
    }

    public function testClassWithStaticFunctionCall()
    {
        $this->frame->setClassName("Test\\Namespace\\TestClass");
        $this->frame->setFunctionName("do_something");
        $this->frame->setType(StackFrame::TYPE_STATIC);

        $this->assertFormat('Test\\Namespace\\<span class="class">TestClass</span>::<span class="function">do_something</span>()');
    }

    public function testNumericArguments()
    {
        $this->assertArguments(
            '<span class="argument argument-integer">1</span>, <span class="argument argument-float">-2.0</span>, <span class="argument argument-float">3.5</span>',
            [1, -2.0, 3.5]
        );
    }

    public function testNullArguments()
    {
        $this->assertArguments(
            '<span class="argument argument-null">null</span>, <span class="argument argument-null">null</span>',
            [null, null]
        );
    }

    public function testBooleanArguments()
    {
        $this->assertArguments(
            '<span class="argument argument-boolean-true">true</span>, <span class="argument argument-boolean-false">false</span>',
            [true, false]
        );
    }

    public function testObjectArguments()
    {
        $this->assertArguments(
            '<span class="argument argument-object">Object[stdClass]</span>, <span class="argument argument-object">Object[Closure]</span>, <span class="argument argument-object">Object[' . TemplateHelper::class . ']</span>',
            [new stdClass(), function () {
            }, new TemplateHelper()]
        );
    }

    public function testStringArguments()
    {
        $this->assertArguments(
            '<span class="argument argument-string">"Lorem ipsum"</span>',
            ["Lorem ipsum"]
        );
    }

    public function testArrayArguments()
    {
        $this->assertArguments(
            '<span class="argument argument-array">array[3]</span>',
            [[1, 2, 3]]
        );
    }

    public function testMixedArgument()
    {
        $file = fopen(__FILE__, "r");
        $this->assertArguments(
            '<span class="argument argument-mixed">resource[stream]</span>',
            [$file]
        );
        fclose($file);
    }

}