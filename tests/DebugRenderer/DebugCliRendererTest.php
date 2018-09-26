<?php

namespace SlashTrace\Tests\DebugRenderer;

use SlashTrace\DebugRenderer\DebugCliRenderer;
use SlashTrace\Exception\ExceptionData;
use SlashTrace\StackTrace\StackFrame;
use SlashTrace\System\CLImateOutput;

/**
 * @covers \SlashTrace\DebugRenderer\DebugCliRenderer
 */
class DebugCliRendererTest extends DebugTextRendererTestCase
{
    protected function createRenderer()
    {
        return new DebugCliRenderer();
    }

    public function testDefaultOutputReceiverIsClImateWrapper()
    {
        $renderer = $this->createRenderer();
        $this->assertInstanceOf(CLImateOutput::class, $renderer->getOutputReceiver());
    }

    public function testTypeIsTagged()
    {
        $exception = new ExceptionData();
        $exception->setType("TestException");

        $this->event->addException($exception);

        $this->assertEquals("<type><bold>TestException</bold></type>", $this->getOutput()[0]);
    }

    public function testMessageIsTagged()
    {
        $exception = new ExceptionData();
        $exception->setType("TestException");
        $exception->setMessage("Test message");

        $this->event->addException($exception);

        $this->assertEquals(
            "<type><bold>TestException</bold></type>: <message>Test message</message>",
            $this->getOutput()[0]
        );
    }

    public function testStackFrameCallIsTagged()
    {
        $frame = new StackFrame();
        $frame->setClassName("TestNamespace\\TestClass");
        $frame->setFunctionName("test");
        $frame->setType(StackFrame::TYPE_METHOD);
        $frame->setArguments([1, 2]);

        $exception = new ExceptionData();
        $exception->setStackTrace([$frame]);

        $this->event->addException($exception);

        $this->assertEquals(
            "  #0 TestNamespace\\<class>TestClass</class>-><function>test</function>(<argument>1</argument>, <argument>2</argument>)",
            $this->getOutput()[3]
        );
    }

    public function testStackFrameLocationIsTagged()
    {
        $frame = new StackFrame();
        $frame->setFunctionName("test");
        $frame->setFile("/var/www/index.php");
        $frame->setLine(100);

        $exception = new ExceptionData();
        $exception->setStackTrace([$frame]);

        $this->event->addException($exception);

        $this->assertEquals(
            "     in <file>/var/www/index.php</file> on line <line>100</line>",
            $this->getOutput()[4]
        );
    }

    public function testBreadcrumbsAreTagged()
    {
        $this->event->addException(new ExceptionData());
        $this->breadcrumbs->record("Breadcrumb 1", [1, 2, 3]);

        $output = $this->getOutput();

        $date = $this->dateTime;
        $date->modify("+30 seconds");

        $this->assertEquals("  #0 [{$date->format("H:i:s")}] <message>Breadcrumb 1</message>", $output[3]);
        $this->assertEquals("     <argument>[1,2,3]</argument>", $output[4]);
    }
}
