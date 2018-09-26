<?php

namespace SlashTrace;

use SlashTrace\EventHandler\EventHandler;
use SlashTrace\System\SystemProvider;

use ErrorException;
use Exception;

class ErrorHandler
{
    /** @var SlashTrace */
    private $slashTrace;

    /** @var SystemProvider */
    private $system;

    /** @var callable|null */
    private $previousErrorHandler;

    /** @var callable|null */
    private $previousExceptionHandler;

    public function __construct(SlashTrace $slashTrace, SystemProvider $system)
    {
        $this->slashTrace = $slashTrace;
        $this->system = $system;
    }

    /**
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     */
    public function onError($level, $message, $file = null, $line = 0)
    {
        if ($level & $this->system->getErrorReportingLevel()) {
            $exception = new ErrorException($message, 0, $level, $file, $line);
            $signal = $this->onException($exception, true);

            if (is_callable($this->previousErrorHandler)) {
                call_user_func($this->previousErrorHandler, $level, $message, $file, $line);
            }

            if ($signal == EventHandler::SIGNAL_EXIT) {
                $this->system->terminate(1);
            }
        }

        return true;
    }

    /**
     * @param Exception $exception
     * @param bool $isError
     * @return int
     */
    public function onException($exception, $isError = false)
    {
        $signal = $this->slashTrace->handleException($exception);
        if ($isError) {
            return $signal;
        }

        if (is_callable($this->previousExceptionHandler)) {
            call_user_func($this->previousExceptionHandler, $exception);
        }

        if ($signal == EventHandler::SIGNAL_EXIT) {
            $this->system->terminate(1);
        }

        return $signal;
    }

    public function onShutdown()
    {
        $error = $this->system->getLastError();
        if (is_null($error)) {
            return;
        }
        if (!Level::isFatal($error["type"])) {
            return;
        }
        $this->onError($error["type"], $error["message"], $error["file"], $error["line"]);
    }

    public function install()
    {
        $system = $this->system;

        $this->previousErrorHandler = $system->setErrorHandler([$this, "onError"]);
        $this->previousExceptionHandler = $system->setExceptionHandler([$this, "onException"]);

        $system->registerShutdownFunction([$this, "onShutdown"]);
    }
}