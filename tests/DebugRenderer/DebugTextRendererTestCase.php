<?php

namespace SlashTrace\Tests\DebugRenderer;

use SlashTrace\Context\EventContext;
use SlashTrace\DebugRenderer\DebugTextRenderer;
use SlashTrace\Event;
use SlashTrace\Tests\Doubles\System\OutputReceiverSpy;
use SlashTrace\Tests\Fixtures\BreadcrumbsFixture;
use SlashTrace\Tests\TestCase;
use DateTime;

abstract class DebugTextRendererTestCase extends TestCase
{
    /** @var DebugTextRenderer */
    protected $renderer;

    /** @var Event */
    protected $event;

    /** @var OutputReceiverSpy */
    protected $outputReceiver;

    /** @var EventContext */
    protected $context;

    /** @var BreadcrumbsFixture */
    protected $breadcrumbs;

    /** @var DateTime */
    protected $dateTime;

    /**
     * @return DebugTextRenderer
     */
    abstract protected function createRenderer();

    protected function setUp()
    {
        parent::setUp();

        $this->renderer = $this->createRenderer();
        $this->outputReceiver = new OutputReceiverSpy();

        $this->renderer->setOutputReceiver($this->outputReceiver);

        $this->event = new Event();
        $this->context = new EventContext();

        $this->dateTime = new DateTime();

        $this->breadcrumbs = new BreadcrumbsFixture();
        $this->breadcrumbs->setTime($this->dateTime);

        $this->context->setBreadcrumbs($this->breadcrumbs);
        $this->event->setContext($this->context);
    }

    protected function getOutput()
    {
        $this->renderer->render($this->event);
        return $this->outputReceiver->getOutput();
    }
}