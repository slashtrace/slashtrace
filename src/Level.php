<?php

namespace SlashTrace;

class Level
{
    const DEBUG = "debug";
    const INFO = "info";
    const WARNING = "warning";
    const ERROR = "error";

    /**
     * @param $level
     * @return bool
     */
    public static function isFatal($level)
    {
        return in_array($level, [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_CORE_WARNING,
            E_COMPILE_WARNING
        ]);
    }

    /**
     * Converts PHP error constants to custom log level
     *
     * @param $severity
     * @return string
     */
    public static function severityToLevel($severity)
    {
        switch ($severity) {
            case E_USER_NOTICE:
            case E_NOTICE:
            case E_STRICT:
                return self::INFO;

            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return self::WARNING;

            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
            default:
                return self::ERROR;
        }
    }
}