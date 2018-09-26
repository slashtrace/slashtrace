<?php

namespace SlashTrace\Tests\Doubles\System;

use SlashTrace\Http\Request;
use SlashTrace\System\SystemProvider;
use DateTime;

class MockSystemProvider extends OutputReceiverSpy implements SystemProvider
{
    /** @var callable */
    private $errorHandler;

    /** @var callable */
    private $exceptionHandler;

    /** @var callable */
    private $shutdownFunction;

    /** @var bool */
    private $isCli = false;

    /** @var Request */
    private $request;

    /** @var int */
    private $errorReporting = E_ALL;

    /** @var array|null */
    private $lastError;

    /** @var array */
    private $serverData = [];

    /** @var DateTime */
    private $dateTime;

    public $cleanOutputBufferCalled = false;

    /** @var bool */
    public $terminateCalled = false;

    /** @var float */
    public $terminateTime = 0.0;

    /** @var bool */
    public $logErrorCalled = false;

    /** @var string */
    public $logErrorArgument;

    public function setErrorHandler(callable $handler)
    {
        $previousHandler = $this->errorHandler;
        $this->errorHandler = $handler;
        return $previousHandler;
    }

    public function setExceptionHandler(callable $handler)
    {
        $previousHandler = $this->exceptionHandler;
        $this->exceptionHandler = $handler;
        return $previousHandler;
    }

    public function registerShutdownFunction(callable $handler)
    {
        $this->shutdownFunction = $handler;
    }

    public function getErrorHandler()
    {
        return $this->errorHandler;
    }

    public function getExceptionHandler()
    {
        return $this->exceptionHandler;
    }

    public function getShutdownFunction()
    {
        return $this->shutdownFunction;
    }

    public function isCli()
    {
        return $this->isCli;
    }

    public function isWeb()
    {
        return !is_null($this->request);
    }

    public function setIsCli()
    {
        $this->isCli = true;
        $this->request = null;
    }

    public function setIsWeb(Request $request = null)
    {
        $this->request = $request ?: new Request();
        $this->isCli = false;
    }

    public function getErrorReportingLevel()
    {
        return $this->errorReporting;
    }

    public function setErrorReporting($errorReporting)
    {
        $this->errorReporting = $errorReporting;
    }

    /**
     * @return array|null
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * @param array|null $error
     */
    public function setLastError($error)
    {
        $this->lastError = $error;
    }

    public function getHttpRequest()
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getServerData()
    {
        return $this->serverData;
    }

    public function setServerData(array $context)
    {
        $this->serverData = $context;
    }

    /**
     * @return DateTime
     */
    public function getDateTime()
    {
        if (is_null($this->dateTime)) {
            return new DateTime();
        }
        return $this->dateTime;
    }

    public function setDateTime(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function cleanOutputBuffer()
    {
        $this->cleanOutputBufferCalled = true;
    }

    public function terminate($status = 0)
    {
        $this->terminateCalled = true;
        $this->terminateTime = microtime(true);
        usleep(1000);
    }

    public function logError($message)
    {
        $this->logErrorCalled = true;
        $this->logErrorArgument = $message;
    }

    public function getXdebugStacktrace()
    {
        return [];
    }
}