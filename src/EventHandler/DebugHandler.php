<?php

namespace SlashTrace\EventHandler;

use SlashTrace\Context\Breadcrumbs;
use SlashTrace\Context\EventContext;
use SlashTrace\Context\User;
use SlashTrace\DebugRenderer\DebugJsonRenderer;
use SlashTrace\DebugRenderer\DebugRenderer;
use SlashTrace\DebugRenderer\DebugCliRenderer;
use SlashTrace\DebugRenderer\DebugWebRenderer;
use SlashTrace\DebugRenderer\DebugTextRenderer;
use SlashTrace\Event;
use SlashTrace\Exception\ExceptionInspector;
use SlashTrace\Level;
use SlashTrace\System\HasSystemProvider;

use ErrorException;
use Exception;

class DebugHandler implements EventHandler
{
    use HasSystemProvider;

    /** @var DebugRenderer */
    private $renderer;

    /** @var ExceptionInspector */
    private $exceptionInspector;

    /** @var EventContext */
    private $eventContext;

    /**
     * @param Exception $exception
     * @return int
     */
    public function handleException($exception)
    {
        $event = $this->createEvent($exception);
        $event->setContext($this->getEventContext());

        return $this->handleEvent($event);
    }

    public function handleEvent(Event $event)
    {
        $this->getRenderer()->render($event);
        return EventHandler::SIGNAL_EXIT;
    }

    /**
     * @return DebugRenderer
     */
    public function getRenderer()
    {
        if (is_null($this->renderer)) {
            $this->renderer = $this->createRenderer();
        }
        return $this->renderer;
    }

    public function setRenderer(DebugRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @return DebugRenderer
     */
    private function createRenderer()
    {
        $system = $this->getSystem();
        if ($system->isCli()) {
            return new DebugCliRenderer();
        }

        $request = $system->getHttpRequest();
        if ($request->getHeader("Accept") === "application/json") {
            return new DebugJsonRenderer();
        }

        if ($request->isXhr()) {
            return new DebugTextRenderer();
        }

        return new DebugWebRenderer();
    }

    /**
     * @param Exception $exception
     * @return Event
     */
    private function createEvent($exception)
    {
        $event = new Event();
        $event->setLevel($this->getLevel($exception));

        $exceptionInspector = $this->getExceptionInspector();
        do {
            $event->addException($exceptionInspector->inspect($exception));
        } while ($exception = $exception->getPrevious());

        return $event;
    }

    private function getExceptionInspector()
    {
        if (is_null($this->exceptionInspector)) {
            $this->exceptionInspector = new ExceptionInspector();
        }
        return $this->exceptionInspector;
    }

    /**
     * @param Exception $exception
     * @return string
     */
    private function getLevel($exception)
    {
        $level = Level::ERROR;
        if ($exception instanceof ErrorException) {
            $level = Level::severityToLevel($exception->getSeverity());
        }
        return $level;
    }

    /**
     * @return EventContext
     */
    private function getEventContext()
    {
        if (is_null($this->eventContext)) {
            $this->eventContext = $this->createEventContext();
        }
        return $this->eventContext;
    }

    /**
     * @return EventContext
     */
    private function createEventContext()
    {
        $system = $this->getSystem();

        $context = new EventContext();
        $context->setServer($system->getServerData());
        $context->setBreadcrumbs(new Breadcrumbs($system));

        if ($system->isWeb()) {
            $context->setHttpRequest($system->getHttpRequest());
        }

        return $context;
    }

    /**
     * @param User $user
     * @throws Exception
     */
    public function setUser(User $user)
    {
        $this->getEventContext()->setUser($user);
    }

    public function recordBreadcrumb($title, array $data = [])
    {
        $this->getEventContext()->getBreadcrumbs()->record($title, $data);
    }

    public function setApplicationPath($path)
    {
        $this->getEventContext()->setApplicationPath($path);
    }

    public function setRelease($release)
    {
        $this->getEventContext()->setRelease($release);
    }
}