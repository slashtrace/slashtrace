<?php

namespace SlashTrace\Tests;

use SlashTrace\ErrorHandler;
use SlashTrace\EventHandler\EventHandler;
use SlashTrace\SlashTrace;
use SlashTrace\System\System;
use SlashTrace\Tests\Doubles\System\MockSystemProvider;

use PHPUnit\Framework\MockObject\MockObject;

use ErrorException;
use Exception;

/**
 * @covers \SlashTrace\ErrorHandler
 */
class ErrorHandlerTest extends TestCase
{
    /** @var ErrorHandler */
    private $handler;

    /** @var MockSystemProvider */
    private $system;

    protected function setUp()
    {
        parent::setUp();

        $this->system = new MockSystemProvider();

        /** @noinspection PhpParamsInspection */
        $this->handler = new ErrorHandler($this->createMock(SlashTrace::class), $this->system);
    }

    /**
     * @param callable $handleCallback
     * @return MockObject|SlashTrace
     */
    private function mockSlashTrace(callable $handleCallback)
    {
        $slashTrace = $this->createMock(SlashTrace::class);
        $slashTrace->expects($this->once())
            ->method("handleException")
            ->willReturnCallback($handleCallback);

        return $slashTrace;
    }

    public function testInstallsHandlers()
    {
        $this->handler->install();

        $this->assertTrue(is_callable($this->system->getErrorHandler()));
        $this->assertTrue(is_callable($this->system->getExceptionHandler()));
        $this->assertTrue(is_callable($this->system->getShutdownFunction()));
    }

    public function testWhenErrorReportingIsDisabled_errorsAreIgnored()
    {
        $this->system->setErrorReporting(0);

        $slashTrace = $this->createMock(SlashTrace::class);
        $slashTrace->expects($this->never())->method("handleException");

        /** @noinspection PhpParamsInspection */
        $handler = new ErrorHandler($slashTrace, $this->system);
        $handler->install();
        $handler->onError(E_USER_WARNING, "Error meesage", __FILE__, __LINE__);
    }

    public function testErrorReportingLevelIsRespected()
    {
        $this->system->setErrorReporting(E_ALL ^ E_USER_WARNING);

        $slashTrace = $this->createMock(SlashTrace::class);
        $slashTrace->expects($this->never())->method("handleException");

        /** @noinspection PhpParamsInspection */
        $handler = new ErrorHandler($slashTrace, $this->system);
        $handler->install();

        $this->assertTrue($handler->onError(E_USER_WARNING, "Error meesage", __FILE__, __LINE__));
    }

    public function testSupressedErrorsAreIgnored()
    {
        $previousErrorReporting = error_reporting(E_ALL);

        $slashTrace = $this->createMock(SlashTrace::class);
        $slashTrace->expects($this->never())->method("handleException");

        /** @noinspection PhpParamsInspection */
        $handler = new ErrorHandler($slashTrace, System::getInstance());

        $handler->install();

        @trigger_error("Error message", E_USER_WARNING);

        error_reporting($previousErrorReporting);
    }

    public function testErrorsAreHandled()
    {
        $severity = E_USER_WARNING;
        $message = "Error message";
        $file = __FILE__;
        $line = __LINE__;

        $handler = new ErrorHandler(
            $this->mockSlashTrace(function (ErrorException $exception) use ($severity, $message, $file, $line) {
                $this->assertEquals($severity, $exception->getSeverity());
                $this->assertEquals($message, $exception->getMessage());
                $this->assertEquals($file, $exception->getFile());
                $this->assertEquals($line, $exception->getLine());

                return EventHandler::SIGNAL_CONTINUE;
            }),
            $this->system
        );

        $handler->onError($severity, $message, $file, $line);
    }

    public function testShutdownErrorsAreHandled()
    {
        $error = [
            "type"    => E_CORE_ERROR,
            "message" => "Error message",
            "file"    => __FILE__,
            "line"    => __LINE__,
        ];

        $this->system->setLastError($error);

        $handler = new ErrorHandler(
            $this->mockSlashTrace(function (ErrorException $exception) use ($error) {
                $this->assertEquals($error["message"], $exception->getMessage());
                $this->assertEquals($error["type"], $exception->getSeverity());
                $this->assertEquals($error["file"], $exception->getFile());
                $this->assertEquals($error["line"], $exception->getLine());

                return EventHandler::SIGNAL_EXIT;
            }),
            $this->system
        );

        $handler->onShutdown();
    }

    public function testNonFatalErrorsAreNotHandledByShutdownHandler()
    {
        $this->system->setLastError([
            "type"    => E_USER_ERROR,
            "message" => "Error message",
            "file"    => __FILE__,
            "line"    => __LINE__,
        ]);

        $slashTrace = $this->createMock(SlashTrace::class);
        $slashTrace->expects($this->never())->method("handleException");

        /** @noinspection PhpParamsInspection */
        $handler = new ErrorHandler($slashTrace, $this->system);
        $handler->onShutdown();
    }

    public function testWhenNoError_shutDownHandlerDoesNothing()
    {
        $this->system->setLastError(null);

        $slashTrace = $this->createMock(SlashTrace::class);
        $slashTrace->expects($this->never())->method("handleException");

        /** @noinspection PhpParamsInspection */
        $handler = new ErrorHandler($slashTrace, $this->system);
        $handler->onShutdown();
    }

    public function testPreviousErrorHandlerIsCalled()
    {
        $expected = [
            "level"   => E_USER_ERROR,
            "message" => "Error message",
            "file"    => __FILE__,
            "line"    => __LINE__,
        ];
        $actual = null;

        $this->system->setErrorHandler(function ($level, $message, $file, $line) use (&$actual) {
            $actual = [
                "level"   => $level,
                "message" => $message,
                "file"    => $file,
                "line"    => $line,
            ];
        });

        $this->handler->install();
        $this->handler->onError(...array_values($expected));

        $this->assertEquals($expected, $actual);
    }

    public function testPreviousExceptionHandlerIsCalled()
    {
        $exception = new Exception();
        $actual = null;

        $this->system->setExceptionHandler(function ($e) use (&$actual) {
            $actual = $e;
        });

        $this->handler->install();
        $this->handler->onException($exception);

        $this->assertSame($exception, $actual);
    }

    public function testPreviousExceptionHandlerIsNotCalledForErrors()
    {
        $called = false;
        $this->system->setExceptionHandler(function () use (&$called) {
            $called = true;
        });

        $this->handler->install();
        $this->handler->onError(E_USER_ERROR, "Error message");

        $this->assertFalse($called);
    }

    public function testExecutionIsNotStoppedWhenServiceReturnContinueSignal()
    {
        $handler = new ErrorHandler(
            $this->mockSlashTrace(function () {
                return EventHandler::SIGNAL_CONTINUE;
            }),
            $this->system
        );

        $handler->onException(new Exception());

        $this->assertFalse($this->system->terminateCalled);
    }

    public function testExecutionIsStoppedWhenServiceReturnsExitSignal()
    {
        $handler = new ErrorHandler(
            $this->mockSlashTrace(function () {
                return EventHandler::SIGNAL_EXIT;
            }),
            $this->system
        );

        $handler->onException(new Exception());

        $this->assertTrue($this->system->terminateCalled);
    }

    public function testPreviousExceptionHandlerIsCalledBeforeExecutionIsTerminated()
    {
        $handlerCallTime = 0.0;

        $this->system->setExceptionHandler(function () use (&$handlerCallTime) {
            $handlerCallTime = microtime(true);
            usleep(1000);
        });

        $handler = new ErrorHandler(
            $this->mockSlashTrace(function () {
                return EventHandler::SIGNAL_EXIT;
            }),
            $this->system
        );
        $handler->install();
        $handler->onException(new Exception());

        $this->assertEquals(-1, bccomp($handlerCallTime, $this->system->terminateTime, 4));
    }

    public function testPreviousErrorHandlerIsCalledBeforeExecutionIsTerminated()
    {
        $handlerCallTime = 0.0;
        $this->system->setErrorHandler(function () use (&$handlerCallTime) {
            $handlerCallTime = microtime(true);
            usleep(1000);
        });

        $handler = new ErrorHandler(
            $this->mockSlashTrace(function () {
                return EventHandler::SIGNAL_EXIT;
            }),
            $this->system
        );
        $handler->install();
        $handler->onError(E_USER_ERROR, "Error message");

        $this->assertEquals(-1, bccomp($handlerCallTime, $this->system->terminateTime, 4));
    }
}