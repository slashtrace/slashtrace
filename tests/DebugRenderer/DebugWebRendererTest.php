<?php

namespace SlashTrace\Tests\DebugRenderer;

use SlashTrace\DebugRenderer\DebugWebRenderer;
use SlashTrace\Event;
use SlashTrace\Exception\ExceptionData;
use SlashTrace\Template\ResourceLoader;
use SlashTrace\Template\TemplateEngine;
use SlashTrace\Tests\Doubles\System\MockSystemProvider;
use SlashTrace\Tests\TestCase;

/**
 * @covers \SlashTrace\DebugRenderer\DebugWebRenderer
 */
class DebugWebRendererTest extends TestCase
{
    /** @var DebugWebRenderer */
    private $renderer;

    /** @var MockSystemProvider */
    private $system;

    protected function setUp()
    {
        parent::setUp();

        $this->system = new MockSystemProvider();

        $this->renderer = new DebugWebRenderer();
        $this->renderer->setSystem($this->system);
    }

    private function mockTemplateEngine(callable $renderCallback = null)
    {
        $templateEngine = $this->createMock(TemplateEngine::class);

        if (!is_null($renderCallback)) {
            $templateEngine->expects($this->once())
                ->method("render")
                ->willReturnCallback(function ($template, array $data) use ($renderCallback) {
                    $this->assertNotEmpty($template);
                    $renderCallback($data);
                });
        }

        /** @noinspection PhpParamsInspection */
        $this->renderer->setTemplateEngine($templateEngine);
    }

    public function testTemplateEngineIsInitializedIfNotSet()
    {
        $renderer = new DebugWebRenderer();
        $this->assertInstanceOf(TemplateEngine::class, $renderer->getTemplateEngine());
    }

    public function testWhenEventHasNoExceptions_pageTitleIsDefault()
    {
        $this->mockTemplateEngine(function (array $data) {
            $this->assertNotEmpty($data["pageTitle"]);
        });
        $this->renderer->render(new Event());
    }

    public function testFirstExceptionMessageIsUsedAsPageTitle()
    {
        $event = new Event();
        $exception = new ExceptionData();
        $exception->setMessage("Test message");
        $event->addException($exception);

        $this->mockTemplateEngine(function (array $data) use ($exception) {
            $this->assertEquals($exception->getMessage(), $data["pageTitle"]);
        });

        $this->renderer->render($event);
    }

    public function testEventIsPassedToTemplate()
    {
        $event = new Event();
        $this->mockTemplateEngine(function (array $data) use ($event) {
            $this->assertSame($event, $data["event"]);
        });
        $this->renderer->render($event);
    }

    public function testResourceLoaderPassedToTemplate()
    {
        $this->mockTemplateEngine(function (array $data) {
            $this->assertInstanceOf(ResourceLoader::class, $data["resourceLoader"]);
        });
        $this->renderer->render(new Event());
    }

    public function testPreviousOutputBuffersAreCleanedBeforeRendering()
    {
        $this->mockTemplateEngine();
        $this->renderer->render(new Event());
        $this->assertTrue($this->system->cleanOutputBufferCalled);
    }
}