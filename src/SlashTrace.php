<?php

namespace SlashTrace;

use SlashTrace\Context\User;
use SlashTrace\EventHandler\EventHandler;
use SlashTrace\EventHandler\EventHandlerException;
use SlashTrace\System\HasSystemProvider;

use Exception;
use RuntimeException;

class SlashTrace
{
    use HasSystemProvider;

    const VERSION = "1.0.0";

    /** @var EventHandler[] */
    private $handlers = [];

    /**
     * @param Exception $exception
     * @return int
     */
    public function handleException($exception)
    {
        foreach ($this->getHandlers() as $handler) {
            try {
                if ($handler->handleException($exception) === EventHandler::SIGNAL_EXIT) {
                    return EventHandler::SIGNAL_EXIT;
                }
            } catch (EventHandlerException $exception) {
                $this->logHandlerException($exception);
                return EventHandler::SIGNAL_EXIT;
            }
        }
        return EventHandler::SIGNAL_CONTINUE;
    }

    private function logHandlerException(EventHandlerException $exception)
    {
        $message = $exception->getMessage();
        $code = $exception->getCode();
        if ($code) {
            $error = sprintf("SlashTrace error (%s): %s", $code, $message);
        } else {
            $error = sprintf("SlashTrace error: %s", $message);
        }

        $this->getSystem()->logError($error);
    }

    /**
     * @return EventHandler[]
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    public function addHandler(EventHandler $handler)
    {
        $this->checkUniqueHandler($handler);
        $this->handlers[] = $handler;
    }

    public function prependHandler(EventHandler $handler)
    {
        $this->checkUniqueHandler($handler);
        array_unshift($this->handlers, $handler);
    }

    /**
     * Checks that a particular handler hasn't already been registered
     *
     * @param EventHandler $input
     * @throws RuntimeException
     */
    private function checkUniqueHandler(EventHandler $input)
    {
        foreach ($this->handlers as $handler) {
            if ($handler === $input) {
                throw new RuntimeException();
            }
        }
    }

    public function register()
    {
        $errorHandler = new ErrorHandler($this, $this->getSystem());
        $errorHandler->install();
    }

    public function setUser(User $user)
    {
        foreach ($this->getHandlers() as $handler) {
            $handler->setUser($user);
        }
    }

    public function recordBreadcrumb($title, array $data = [])
    {
        foreach ($this->getHandlers() as $handler) {
            $handler->recordBreadcrumb($title, $data);
        }
    }

    public function setRelease($release)
    {
        foreach ($this->getHandlers() as $handler) {
            $handler->setRelease($release);
        }
    }

    public function setApplicationPath($path)
    {
        foreach ($this->getHandlers() as $handler) {
            $handler->setApplicationPath($path);
        }
    }
}
