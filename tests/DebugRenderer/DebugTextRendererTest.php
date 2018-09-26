<?php

namespace SlashTrace\Tests\DebugRenderer;

use LogicException;
use SlashTrace\DebugRenderer\DebugTextRenderer;
use SlashTrace\Event;
use SlashTrace\Exception\ExceptionData;
use SlashTrace\Formatter\StackFrameCallTextFormatter;
use SlashTrace\StackTrace\StackFrame;
use SlashTrace\System\SystemProvider;
use SlashTrace\Tests\Doubles\System\OutputReceiverSpy;

use ErrorException;
use stdClass;

/**
 * @covers \SlashTrace\DebugRenderer\DebugTextRenderer
 */
class DebugTextRendererTest extends DebugTextRendererTestCase
{
    protected function createRenderer()
    {
        return new DebugTextRenderer();
    }

    public function testDefaultOutputReceiverIsSystemInstance()
    {
        $renderer = $this->createRenderer();
        $this->assertInstanceOf(SystemProvider::class, $renderer->getOutputReceiver());
    }

    public function testCanSetOutputReceiver()
    {
        $renderer = $this->createRenderer();
        $output = new OutputReceiverSpy();

        $renderer->setOutputReceiver($output);
        $this->assertSame($output, $renderer->getOutputReceiver());
    }

    public function testEmptyEventReturnsEmptyString()
    {
        $output = $this->getOutput();
        $this->assertEquals(1, count($output));
        $this->assertEquals("", $output[0]);
    }

    public function testExceptionWithNoMessageOrTrace()
    {
        $type = LogicException::class;

        $exception = new ExceptionData();
        $exception->setType($type);

        $this->event->addException($exception);

        $output = $this->getOutput();
        $this->assertEquals(1, count($output));
        $this->assertEquals("$type", $output[0]);
    }

    public function testExceptionWithMessageAndNoTrace()
    {
        $type = LogicException::class;
        $message = "Something went wrong!";

        $exception = new ExceptionData();
        $exception->setType($type);
        $exception->setMessage($message);

        $this->event->addException($exception);

        $output = $this->getOutput();
        $this->assertEquals(1, count($output));
        $this->assertEquals("$type: $message", $output[0]);
    }

    public function testStackFrameCall()
    {
        $frame = new StackFrame();
        $frame->setClassName("TestClass");
        $frame->setFunctionName("test");
        $frame->setType(StackFrame::TYPE_METHOD);

        $exception = new ExceptionData();
        $exception->setType(LogicException::class);
        $exception->setStackTrace([$frame]);

        $this->event->addException($exception);

        $formatter = new StackFrameCallTextFormatter();
        $expected = $formatter->format($frame);

        $output = $this->getOutput();
        $this->assertEquals(4, count($output));
        $this->assertEquals("  Stack trace:", $output[2]);
        $this->assertEquals("  #0 $expected", $output[3]);
    }

    public function testStackFrameLocation()
    {
        $frame = new StackFrame();
        $frame->setFunctionName("test");
        $frame->setFile("/var/www/application/index.php");
        $frame->setLine(100);

        $exception = new ExceptionData();
        $exception->setType(LogicException::class);
        $exception->setStackTrace([$frame]);

        $this->event->addException($exception);
        $this->context->setApplicationPath("/var/www");

        $output = $this->getOutput();

        $this->assertEquals(5, count($output));
        $this->assertEquals("     in application/index.php on line 100", $output[4]);
    }

    public function testStackTraceLocationIsIndentedAccordingToFrameIndexLength()
    {
        $frame = new StackFrame();
        $frame->setFunctionName("test");
        $frame->setFile("/var/www/index.php");
        $frame->setLine(100);

        $exception = new ExceptionData();
        $exception->setType(LogicException::class);
        $exception->setStackTrace([10 => $frame]);

        $this->event->addException($exception);

        $output = $this->getOutput();

        $this->assertEquals(str_repeat(" ", 6), substr($output[4], 0, 6));
    }

    public function testStackFramesAreIndexed()
    {
        $frameA = new StackFrame();
        $frameA->setFunctionName("test");

        $frameB = new StackFrame();
        $frameB->setFunctionName("test");

        $exception = new ExceptionData();
        $exception->setType(LogicException::class);
        $exception->setStackTrace([$frameA, $frameB]);

        $this->event->addException($exception);

        $output = $this->getOutput();

        $this->assertEquals("#0", substr($output[3], 2, 2));
        $this->assertEquals("#1", substr($output[5], 2, 2));
    }

    public function testMultipleExceptions()
    {
        $exceptionA = new ExceptionData();
        $exceptionA->setType(LogicException::class);

        $exceptionB = new ExceptionData();
        $exceptionB->setType(ErrorException::class);

        $this->event->addException($exceptionA);
        $this->event->addException($exceptionB);

        $output = $this->getOutput();
        $this->assertEquals("Previous exception:", $output[2]);
        $this->assertEquals($exceptionB->getType(), $output[3]);
    }

    public function testEventWithoutContext()
    {
        $exception = new ExceptionData();
        $exception->setType(LogicException::class);

        $event = new Event();
        $event->addException($exception);

        $this->renderer->render($event);
        $output = $this->outputReceiver->getOutput();

        $this->assertEquals(1, count($output));
    }

    public function testBreadcrumbsWithoutData()
    {
        $this->event->addException(new ExceptionData());

        $this->breadcrumbs->record("Breadcrumb 1");
        $this->breadcrumbs->record("Breadcrumb 2");

        $output = $this->getOutput();

        $date = $this->dateTime;

        $this->assertEquals("Breadcrumbs:", $output[2]);

        $date->modify("+30 seconds");
        $this->assertEquals("  #0 [{$date->format("H:i:s")}] Breadcrumb 1", $output[3]);

        $date->modify("+30 seconds");
        $this->assertEquals("  #1 [{$date->format("H:i:s")}] Breadcrumb 2", $output[5]);
    }

    public function testBreadcrumbData()
    {
        $this->event->addException(new ExceptionData());

        $address = new stdClass();
        $address->street = "Test street";
        $address->number = 100;

        $this->breadcrumbs->record("Breadcrumb 1", [
            "name" => "John Doe",
            "age" => 32,
            "address" => $address
        ]);

        $output = $this->getOutput();

        $this->assertEquals(
            '     {"name":"John Doe","age":32,"address":{"street":"Test street","number":100}}',
            $output[4]
        );
    }
}