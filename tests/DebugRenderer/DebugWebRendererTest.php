<?php

namespace SlashTrace\Tests\DebugRenderer;

use SlashTrace\DebugRenderer\DebugWebRenderer;
use SlashTrace\Event;
use SlashTrace\Exception\ExceptionData;
use SlashTrace\Template\ResourceLoader;
use SlashTrace\Template\TemplateEngine;
use SlashTrace\Tests\Doubles\System\MockSystemProvider;
use SlashTrace\Tests\Doubles\Template\MockTemplateEngine;
use SlashTrace\Tests\TestCase;

/**
 * @covers \SlashTrace\DebugRenderer\DebugWebRenderer
 */
class DebugWebRendererTest extends TestCase
{
    /** @var DebugWebRenderer */
    private $renderer;

    /** @var MockTemplateEngine */
    private $templateEngine;

    /** @var MockSystemProvider */
    private $system;

    protected function setUp()
    {
        parent::setUp();

        $this->templateEngine = new MockTemplateEngine();

        $this->system = new MockSystemProvider();

        $this->renderer = new DebugWebRenderer();
        $this->renderer->setSystem($this->system);
        $this->renderer->setTemplateEngine($this->templateEngine);
    }

    private function getTemplateData($key)
    {
        return $this->templateEngine->getRenderData()[$key];
    }

    public function testTemplateEngineIsInitializedIfNotSet()
    {
        $renderer = new DebugWebRenderer();
        $this->assertInstanceOf(TemplateEngine::class, $renderer->getTemplateEngine());
    }

    public function testWhenEventHasNoExceptions_pageTitleIsDefault()
    {
        $this->renderer->render(new Event());
        $this->assertTrue(strlen($this->getTemplateData("pageTitle")) > 0);
    }

    public function testFirstExceptionMessageIsUsedAsPageTitle()
    {
        $event = new Event();
        $exception = new ExceptionData();
        $exception->setMessage("Test message");
        $event->addException($exception);

        $this->renderer->render($event);

        $this->assertEquals($exception->getMessage(), $this->getTemplateData("pageTitle"));
    }

    public function testEventIsPassedToTemplate()
    {
        $event = new Event();
        $this->renderer->render($event);
        $this->assertSame($event, $this->getTemplateData("event"));
    }

    public function testResourceLoaderPassedToTemplate()
    {
        $this->renderer->render(new Event());
        $this->assertInstanceOf(ResourceLoader::class, $this->getTemplateData("resourceLoader"));
    }

    public function testPreviousOutputBuffersAreCleanedBeforeRendering()
    {
        $this->renderer->render(new Event());
        $this->assertTrue($this->system->cleanOutputBufferCalled);
    }
}