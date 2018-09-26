<?php

namespace SlashTrace\System;

use SlashTrace\Http\Request;
use DateTime;

interface SystemProvider extends OutputReceiver
{
    public function setErrorHandler(callable $handler);

    public function setExceptionHandler(callable $handler);

    public function registerShutdownFunction(callable $handler);

    /**
     * @return bool
     */
    public function isCli();

    /**
     * @return bool
     */
    public function isWeb();

    /**
     * @return int
     */
    public function getErrorReportingLevel();

    /**
     * @return array|null
     */
    public function getLastError();

    /**
     * @return Request|null
     */
    public function getHttpRequest();

    /**
     * @return array
     */
    public function getServerData();

    /**
     * @return DateTime
     */
    public function getDateTime();

    public function cleanOutputBuffer();

    /**
     * @param int $status
     */
    public function terminate($status = 0);

    public function logError($message);

    /**
     * @return array
     */
    public function getXdebugStacktrace();
}