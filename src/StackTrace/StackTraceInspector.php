<?php

namespace SlashTrace\StackTrace;

use ErrorException;
use Exception;
use SlashTrace\ErrorHandler;
use SlashTrace\Level;
use SlashTrace\System\HasSystemProvider;

class StackTraceInspector
{
    use HasSystemProvider;

    /** @var StackFrameContextExtractor */
    private $contextExtractor;

    /**
     * @param Exception $exception
     * @return StackFrame[]
     */
    public function fromException($exception)
    {
        $frames = [];
        $frames[] = $this->getExceptionFrame($exception);

        if ($exception instanceof ErrorException && Level::isFatal($exception->getSeverity())) {
            $stacktrace = $this->getSystem()->getXdebugStacktrace();
        } else {
            $stacktrace = $exception->getTrace();
        }

        foreach ($stacktrace as $frame) {
            if (isset($frame["class"]) && $frame["class"] == ErrorHandler::class) {
                continue;
            }
            $frames[] = $this->createFrame($frame);
        }

        return $frames;
    }

    /**
     * @param array $data
     * @return StackFrame
     */
    public function createFrame(array $data)
    {
        $frame = new StackFrame();

        $this->setFrameLocation($frame, $data);
        $this->setFrameData($frame, $data);

        return $frame;
    }

    private function setFrameLocation(StackFrame $stackFrame, array $data)
    {
        if (!isset($data["file"])) {
            return;
        }

        if (preg_match('/^(.*)\((\d+)\) : (?:eval\(\)\'d|assert) code$/', $data["file"], $matches)) {
            $data["file"] = $matches[1];
            $data["line"] = intval($matches[2]);
        }

        $stackFrame->setFile($data["file"]);

        if (isset($data["line"])) {
            $stackFrame->setLine($data["line"]);
            $this->setFrameContext($stackFrame);
        }
    }

    private function setFrameData(StackFrame $frame, array $data)
    {
        if (isset($data["class"])) {
            $frame->setClassName($data["class"]);
        }
        if (isset($data["function"])) {
            $frame->setFunctionName($data["function"]);
        }
        if (isset($data["type"])) {
            $frame->setType($data["type"]);
        }
        if (isset($data["args"])) {
            $frame->setArguments($data["args"]);
        }
    }

    /**
     * @param Exception $exception
     * @return StackFrame
     */
    public function getExceptionFrame($exception)
    {
        $frame = new StackFrame();
        $frame->setFile($exception->getFile());
        $frame->setLine($exception->getLine());
        $frame->setClassName(get_class($exception));

        if ($frame->getFile()) {
            $this->setFrameContext($frame);
        }

        return $frame;
    }

    private function setFrameContext(StackFrame $frame)
    {
        $extractor = $this->getContextExtractor();
        $context = $extractor->getContext($frame->getFile(), $frame->getLine());
        $frame->setContext($context);
    }

    public function getContextExtractor()
    {
        if (is_null($this->contextExtractor)) {
            $this->contextExtractor = new StackFrameContextExtractor();
        }
        return $this->contextExtractor;
    }

    public function setContextExtractor(StackFrameContextExtractor $contextExtractor)
    {
        $this->contextExtractor = $contextExtractor;
    }
}