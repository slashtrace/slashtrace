<?php

namespace SlashTrace\System;

use SlashTrace\Http\Request;
use SlashTrace\StackTrace\StackFrame;

use DateTime;

class System implements SystemProvider
{
    private static $instance;

    /** @var Request */
    private $request;

    private function __construct()
    {
    }

    public function setErrorHandler(callable $handler)
    {
        return set_error_handler($handler);
    }

    public function setExceptionHandler(callable $handler)
    {
        return set_exception_handler($handler);
    }

    public function registerShutdownFunction(callable $handler)
    {
        register_shutdown_function($handler);
    }

    public function isCli()
    {
        return php_sapi_name() === "cli";
    }

    public function isWeb()
    {
        return !$this->isCli();
    }

    public function getErrorReportingLevel()
    {
        return error_reporting();
    }

    public function getFileContents($path)
    {
        return file_get_contents($path);
    }

    public function fileExists($path)
    {
        return file_exists($path);
    }

    public function getLastError()
    {
        return error_get_last();
    }

    public function getHttpRequest()
    {
        if (!$this->isWeb()) {
            return null;
        }
        if (is_null($this->request)) {
            $this->request = new Request();
        }
        return $this->request;
    }

    /**
     * @return array
     */
    public function getServerData()
    {
        return $_SERVER;
    }

    /**
     * @return DateTime
     */
    public function getDateTime()
    {
        return new DateTime();
    }

    public function cleanOutputBuffer()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    public function terminate($status = 0)
    {
        exit($status);
    }

    public function output($string)
    {
        echo("$string\n");
    }

    public function logError($message)
    {
        error_log($message);
    }

    public function getXdebugStacktrace()
    {
        if (!extension_loaded("xdebug")) {
            return [];
        }
        $trace = [];
        foreach (xdebug_get_function_stack() as $xFrame) {
            $trace[] = $this->convertXdebugFrame($xFrame);
        }
        return array_diff_key(
            array_reverse($trace),
            debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        );
    }

    /**
     * @param array $frame
     * @return array
     */
    private function convertXdebugFrame(array $frame)
    {
        return array_filter([
            "file"     => $frame["file"],
            "line"     => $frame["line"],
            "function" => $this->getXdebugFrameFunction($frame),
            "class"    => isset($frame["class"]) ? $frame["class"] : null,
            "type"     => $this->getXdebugStackFrameType($frame),
            "args"     => $this->getXdebugStackFrameArguments($frame),
        ]);
    }

    /**
     * @param array $frame
     * @return string
     */
    private function getXdebugStackFrameType(array $frame)
    {
        if (!isset($frame["type"])) {
            return StackFrame::TYPE_FUNCTION;
        }
        if ($frame["type"] == "dynamic") {
            return StackFrame::TYPE_METHOD;
        }
        return StackFrame::TYPE_STATIC;
    }

    private function getXdebugFrameFunction(array $frame)
    {
        if (isset($frame["function"])) {
            return $frame["function"];
        }
        if (isset($frame["include_filename"])) {
            return "include";
        }
        return null;
    }

    private function getXdebugStackFrameArguments(array $frame)
    {
        if (isset($frame["include_filename"])) {
            return [$frame["include_filename"]];
        }
        return isset($frame["params"]) ? $frame["params"] : [];
    }

    /**
     * @return System
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }
}
