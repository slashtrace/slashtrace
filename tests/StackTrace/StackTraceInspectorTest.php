<?php

namespace SlashTrace\Tests\StackTrace;

use SlashTrace\ErrorHandler;
use SlashTrace\StackTrace\StackFrame;
use SlashTrace\StackTrace\StackFrameContextExtractor;
use SlashTrace\StackTrace\StackTraceInspector;
use SlashTrace\System\SystemProvider;
use SlashTrace\Tests\Doubles\StackTrace\MockStackFrameContextExtractor;
use SlashTrace\Tests\TestCase;

use LogicException;
use Exception;
use ErrorException;
use stdClass;

class StackTraceInspectorTest extends TestCase
{
    /** @var StackTraceInspector */
    private $inspector;

    /** @var MockStackFrameContextExtractor */
    private $contextExtractor;

    protected function setUp()
    {
        parent::setUp();

        $this->inspector = new StackTraceInspector();

        $this->contextExtractor = new MockStackFrameContextExtractor();
        $this->contextExtractor->setContextResult(["Line 1", "Line 2", "Line 3"]);

        $this->inspector->setContextExtractor($this->contextExtractor);
    }

    private function assertContextCall($index, $file, $line)
    {
        $contextCalls = $this->contextExtractor->getContextCalls();

        $this->assertEquals(str_replace("\\", "/", $file), $contextCalls[$index][0]);
        $this->assertEquals($line, $contextCalls[$index][1]);
    }

    private function assertFrameLocationAndContext(StackFrame $frame, $file, $line)
    {
        $this->assertEquals(str_replace("\\", "/", $file), $frame->getFile());
        $this->assertEquals($line, $frame->getLine());

        $this->assertContextCall(0, $file, $line);
        $this->assertEquals($this->contextExtractor->getContextResult(), $frame->getContext());
    }

    public function testWhenNoStackFrameContextExtractorProvided_defaultIsReturned()
    {
        $inspector = new StackTraceInspector();
        $this->assertInstanceOf(StackFrameContextExtractor::class, $inspector->getContextExtractor());
    }

    public function testCanSetStackFrameContextExtractor()
    {
        $inspector = new StackTraceInspector();
        $extractor = new StackFrameContextExtractor();
        $inspector->setContextExtractor($extractor);
        $this->assertSame($extractor, $inspector->getContextExtractor());
    }

    public function testFirstFrameInStacktraceMatchesExceptionFileAndLine()
    {
        $exception = new LogicException();
        $trace = $this->inspector->fromException($exception);

        $this->assertFrameLocationAndContext(
            $trace[0],
            $exception->getFile(),
            $exception->getLine()
        );
    }

    public function testFirstFrameInStackTraceForErrorException()
    {
        $errorException = new ErrorException("Message", 12345, E_USER_WARNING, "/some/file", 100);
        $trace = $this->inspector->fromException($errorException);

        $this->assertFrameLocationAndContext(
            $trace[0],
            $errorException->getFile(),
            $errorException->getLine()
        );
        $this->assertEquals(ErrorException::class, $trace[0]->getClassName());
    }

    public function testStackTraceFromArray()
    {
        $data = [
            "file"     => __FILE__,
            "line"     => __LINE__,
            "class"    => __CLASS__,
            "function" => __METHOD__,
            "type"     => "->",
            "args"     => ["argument 1", "argument 2", new stdClass()]
        ];

        $frame = $this->inspector->createFrame($data);

        $this->assertFrameLocationAndContext($frame, $data["file"], $data["line"]);

        $this->assertEquals($data["class"], $frame->getClassName());
        $this->assertEquals($data["function"], $frame->getFunctionName());
        $this->assertEquals(StackFrame::TYPE_METHOD, $frame->getType());
        $this->assertEquals($data["args"], $frame->getArguments());
    }

    public function testWhenNoFrameFile_contextIsNotExtracted()
    {
        $this->inspector->createFrame([
            "class"    => "SomeClass",
            "function" => "doSomething"
        ]);
        $this->assertEmpty($this->contextExtractor->getContextCalls());
    }

    public function testFileAndLineIsExtractedFromEvaledCode()
    {
        $frame = $this->inspector->createFrame([
            "file" => "/some/file.php(20) : eval()'d code"
        ]);
        $this->assertFrameLocationAndContext($frame, "/some/file.php", 20);
    }

    public function testFileAndLineIsExtractedFromAssertionCode()
    {
        $frame = $this->inspector->createFrame([
            "file" => "/some/file.php(20) : assert code"
        ]);
        $this->assertFrameLocationAndContext($frame, "/some/file.php", 20);
    }

    public function testWhenExceptionHasNoFileAndLine_contextIsNotExtracted()
    {
        $exception = new ErrorException("Error message", 1234, E_USER_ERROR, null, null);
        $this->inspector->getExceptionFrame($exception);

        $this->assertEmpty($this->contextExtractor->getContextCalls());
    }

    public function testFramesFromErrorHandlerAreSkipped()
    {
        $exception = $this->createPartialMock(stdClass::class, ["getFile", "getLine", "getTrace"]);
        $exception->method("getFile")->willReturn(__FILE__);
        $exception->method("getLine")->willReturn(__LINE__);
        $exception->method("getTrace")->willReturn([
            [
                "class" => Exception::class,
                "file"  => __FILE__,
                "line"  => __LINE__
            ],
            [
                "class" => ErrorHandler::class,
                "file"  => __FILE__,
                "line"  => __LINE__
            ],
            [
                "class" => get_class($this),
                "file"  => __FILE__,
                "line"  => __LINE__
            ]
        ]);

        /** @noinspection PhpParamsInspection */
        $trace = $this->inspector->fromException($exception);

        $this->assertEquals(3, count($trace));
    }

    public function testWhenExceptionIsFatalErrorException_xdebugStackTraceIsUsed()
    {
        $stacktrace = [
            [
                "class" => __CLASS__,
                "file"  => "/some/file",
                "line"  => __LINE__
            ],
            [
                "class" => __CLASS__,
                "file"  => "/some/other/file",
                "line"  => __LINE__
            ]
        ];

        $system = $this->createMock(SystemProvider::class);
        $system->expects($this->once())
            ->method("getXdebugStacktrace")
            ->willReturn($stacktrace);

        /** @noinspection PhpParamsInspection */
        $this->inspector->setSystem($system);

        $exception = new ErrorException("Lorem ipsum", 0, E_ERROR);
        $trace = $this->inspector->fromException($exception);

        $this->assertEquals(3, count($trace));
        array_shift($trace);

        foreach ($trace as $i => $frame) {
            $this->assertEquals($stacktrace[$i]["class"], $frame->getClassName());
            $this->assertEquals($stacktrace[$i]["file"], $frame->getFile());
            $this->assertEquals($stacktrace[$i]["line"], $frame->getLine());
        }
    }

}