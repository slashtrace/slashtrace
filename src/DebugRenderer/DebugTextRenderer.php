<?php

namespace SlashTrace\DebugRenderer;

use SlashTrace\Context\Breadcrumbs;
use SlashTrace\Context\Breadcrumbs\Breadcrumb;
use SlashTrace\Event;
use SlashTrace\Exception\ExceptionData;
use SlashTrace\Formatter\StackFrameCallFormatter;
use SlashTrace\Formatter\StackFrameCallTextFormatter;
use SlashTrace\Serializer\Serializer;
use SlashTrace\StackTrace\StackFrame;
use SlashTrace\System\OutputReceiver;
use SlashTrace\System\System;
use SlashTrace\System\SystemProvider;

class DebugTextRenderer implements DebugRenderer
{
    const INDENT = 2;

    /** @var StackFrameCallFormatter */
    private $stackFrameCallFormatter;

    /** @var OutputReceiver */
    private $outputReceiver;

    /** @var Event */
    private $event;

    public function render(Event $event)
    {
        $this->event = $event;

        $this->renderExceptions();
        $this->renderContext();
    }

    private function renderExceptions()
    {
        $exceptions = $this->event->getExceptions();
        if (!count($exceptions)) {
            $this->out("");
            return;
        }

        foreach ($exceptions as $i => $exception) {
            if ($i > 0) {
                $this->out("");
                $this->out("Previous exception:");
            }
            $this->renderException($exception);
        }
    }

    private function renderException(ExceptionData $exception)
    {
        $this->renderExceptionType($exception);

        $trace = $exception->getStackTrace();
        if (count($trace)) {
            $this->out("");
            $this->renderStackTrace($trace);
        }
    }

    private function renderExceptionType(ExceptionData $exception)
    {
        $type = $exception->getType();
        $message = $exception->getMessage();

        if ($message) {
            $this->out(sprintf(
                "%s: %s",
                $this->formatType($type),
                $this->formatMessage($message)
            ));
        } else {
            $this->out($this->formatType($type));
        }
    }

    /**
     * @param StackFrame[] $trace
     */
    private function renderStackTrace(array $trace)
    {
        $this->out("Stack trace:", self::INDENT);
        foreach ($trace as $i => $frame) {
            $this->renderStackFrame($frame, $i);
            if ($i < count($trace) - 1) {
                $this->out("");
            }
        }
    }

    private function renderStackFrame(StackFrame $frame, $index)
    {
        $this->out(
            sprintf("#%d %s", $index, $this->formatStackFrameCall($frame)),
            self::INDENT
        );

        if ($frame->getFile()) {
            $indent = self::INDENT + 2 + strlen($index);
            $this->out($this->formatStackFrameLocation($frame), $indent);
        }
    }

    private function formatStackFrameCall(StackFrame $frame)
    {
        return $this->getStackFrameCallFormatter()->format($frame);
    }

    private function formatStackFrameLocation(StackFrame $frame)
    {
        return sprintf(
            "in %s on line %s",
            $this->formatFile($frame->getRelativeFile($this->getApplicationPath())),
            $this->formatLine($frame->getLine())
        );
    }

    private function renderContext()
    {
        $context = $this->event->getContext();
        if (is_null($context)) {
            return;
        }

        $breadcrumbs = $context->getBreadcrumbs();
        if (!is_null($breadcrumbs)) {
            $this->renderBreadcrumbs($breadcrumbs);
        }
    }

    private function renderBreadcrumbs(Breadcrumbs $breadcrumbs)
    {
        $crumbs = $breadcrumbs->getCrumbs();
        if (!count($crumbs)) {
            return;
        }

        $this->out("");
        $this->out("Breadcrumbs:");
        foreach ($crumbs as $i => $crumb) {
            $this->renderBreadcrumb($crumb, $i);
            if ($i < count($crumbs) - 1) {
                $this->out("");
            }
        }
    }

    private function renderBreadcrumb(Breadcrumb $crumb, $index)
    {
        $title = sprintf(
            "#%d [%s] %s",
            $index,
            $crumb->getDateTime()->format("H:i:s"),
            $this->formatMessage($crumb->getTitle())
        );

        $this->out($title, 2);

        $data = $crumb->getData();
        if (!$data) {
            return;
        }
        $this->out($this->renderBreadcrumbData($data), 4 + strlen($index));
    }

    protected function renderBreadcrumbData(array $data)
    {
        $serializer = new Serializer();
        return json_encode($serializer->serialize($data));
    }

    private function out($string, $indent = 0)
    {
        $indent = str_repeat(" ", $indent);
        $this->getOutputReceiver()->output($indent . $string);
    }

    /**
     * @return OutputReceiver
     */
    public function getOutputReceiver()
    {
        if (is_null($this->outputReceiver)) {
            $this->outputReceiver = $this->initOutputReceiver();
        }
        return $this->outputReceiver;
    }

    /**
     * @return SystemProvider
     */
    protected function initOutputReceiver()
    {
        return System::getInstance();
    }

    public function setOutputReceiver(OutputReceiver $receiver)
    {
        $this->outputReceiver = $receiver;
    }

    /**
     * @return StackFrameCallFormatter
     */
    public function getStackFrameCallFormatter()
    {
        if (is_null($this->stackFrameCallFormatter)) {
            $this->stackFrameCallFormatter = $this->initStackFrameCallFormatter();
        }
        return $this->stackFrameCallFormatter;
    }

    protected function initStackFrameCallFormatter()
    {
        return new StackFrameCallTextFormatter();
    }

    protected function formatType($type)
    {
        return $type;
    }

    protected function formatMessage($message)
    {
        return $message;
    }

    protected function formatFile($file)
    {
        return $file;
    }

    protected function formatLine($line)
    {
        return $line;
    }

    /**
     * @return string
     */
    private function getApplicationPath()
    {
        $context = $this->event->getContext();
        return $context ? $context->getApplicationPath() : null;
    }
}