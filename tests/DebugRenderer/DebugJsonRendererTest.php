<?php

namespace SlashTrace\Tests\DebugRenderer;

use SlashTrace\DebugRenderer\DebugJsonRenderer;
use SlashTrace\Event;
use SlashTrace\Tests\Doubles\System\MockSystemProvider;
use SlashTrace\Tests\TestCase;

class DebugJsonRendererTest extends TestCase
{
    /** @var DebugJsonRenderer */
    private $renderer;

    /** @var MockSystemProvider */
    private $system;

    protected function setUp()
    {
        parent::setUp();

        $this->system = new MockSystemProvider();

        $this->renderer = new DebugJsonRenderer();
        $this->renderer->setSystem($this->system);
    }

    public function testOutput()
    {
        $event = $this->createMock(Event::class);
        $event->expects($this->once())
            ->method("jsonSerialize")
            ->willReturn(["foo" => "bar"]);

        /** @noinspection PhpParamsInspection */
        $this->renderer->render($event);

        $this->assertEquals(
            json_encode([
                "success" => false,
                "foo"     => "bar",
            ]),
            $this->system->getOutput()[0]
        );
    }
}