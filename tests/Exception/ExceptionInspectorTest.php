<?php

namespace SlashTrace\Exception;

use LogicException;
use SlashTrace\StackTrace\StackFrame;
use SlashTrace\StackTrace\StackTraceInspector;
use SlashTrace\Tests\Doubles\StackTrace\MockStackTraceInspector;
use SlashTrace\Tests\TestCase;
use Exception;

class ExceptionInspectorTest extends TestCase
{

    /** @var ExceptionInspector */
    private $inspector;

    protected function setUp()
    {
        parent::setUp();
        $this->inspector = new ExceptionInspector();
    }

    public function testWhenNoStackTraceInsectorProvided_defaultIsReturned()
    {
        $this->assertInstanceOf(StackTraceInspector::class, $this->inspector->getStackTraceInspector());
    }

    public function testCanSetStackFrameInspector()
    {
        $inspector = new StackTraceInspector();
        $this->inspector->setStackTraceInspector($inspector);
        $this->assertSame($inspector, $this->inspector->getStackTraceInspector());
    }

    public function testBasicExceptionData()
    {
        $exception = new LogicException("Message", 12345);
        $exceptionData = $this->inspector->inspect($exception);

        $this->assertEquals($exceptionData->getMessage(), $exception->getMessage());
        $this->assertEquals($exceptionData->getType(), LogicException::class);
    }

    public function testStackTraceInspectorIntegration()
    {
        $stackFrames = [new StackFrame(), new StackFrame()];

        $inspector = new MockStackTraceInspector();
        $inspector->setStackTrace($stackFrames);

        $this->inspector->setStackTraceInspector($inspector);

        $exceptionData = $this->inspector->inspect(new Exception());
        $stackTrace = $exceptionData->getStackTrace();

        foreach ($stackFrames as $i => $frame) {
            $this->assertSame($frame, $stackTrace[$i]);
        }
    }

}