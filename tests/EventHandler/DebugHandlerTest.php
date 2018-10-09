<?php

namespace SlashTrace\Tests\EventHandler;

use SlashTrace\Context\EventContext;
use SlashTrace\Context\User;
use SlashTrace\DebugRenderer\DebugJsonRenderer;
use SlashTrace\DebugRenderer\DebugRenderer;
use SlashTrace\Http\Request;
use SlashTrace\DebugRenderer\DebugCliRenderer;
use SlashTrace\DebugRenderer\DebugWebRenderer;
use SlashTrace\DebugRenderer\DebugTextRenderer;
use SlashTrace\Event;
use SlashTrace\EventHandler\DebugHandler;
use SlashTrace\EventHandler\EventHandler;

use SlashTrace\Level;
use SlashTrace\Tests\Doubles\System\MockSystemProvider;
use SlashTrace\Tests\TestCase;

use PHPUnit\Framework\MockObject\MockObject;
use ErrorException;
use Exception;
use RuntimeException;

class DebugHandlerTest extends TestCase
{
    /** @var DebugHandler */
    private $handler;

    /** @var MockSystemProvider */
    private $system;

    protected function setUp()
    {
        parent::setUp();

        $this->system = new MockSystemProvider();

        $this->handler = new DebugHandler();
        $this->handler->setSystem($this->system);
    }

    private function handleException(Exception $e = null)
    {
        return $this->handler->handleException($e ?: new Exception());
    }

    /**
     * @param callable|null $renderCallback
     * @return MockObject|DebugRenderer
     */
    private function mockRenderer(callable $renderCallback = null)
    {
        $renderer = $this->createMock(DebugRenderer::class);

        if (!is_null($renderCallback)) {
            $renderer->expects($this->once())
                ->method("render")
                ->willReturnCallback($renderCallback);
        }

        /** @noinspection PhpParamsInspection */
        $this->handler->setRenderer($renderer);

        return $renderer;
    }

    public function testRendererSelectionWhenCli()
    {
        $this->system->setIsCli();
        $this->assertInstanceOf(DebugCliRenderer::class, $this->handler->getRenderer());
    }

    public function testRendererSelectionWhenWeb()
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method("isXhr")
            ->willReturn(false);

        $this->system->setIsWeb($request);

        $this->assertInstanceOf(DebugWebRenderer::class, $this->handler->getRenderer());
    }

    public function testRendererWhenXhr()
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method("isXhr")
            ->willReturn(true);

        $this->system->setIsWeb($request);

        $this->assertInstanceOf(DebugTextRenderer::class, $this->handler->getRenderer());
    }

    public function testRendererSelectionWhenJsonRequest()
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->once())
            ->method("getHeader")
            ->with("Accept")
            ->willReturn("application/json");

        $this->system->setIsWeb($request);
        $this->assertInstanceOf(DebugJsonRenderer::class, $this->handler->getRenderer());
    }

    public function testCanSetRenderer()
    {
        $renderer = new DebugCliRenderer();
        $this->handler->setRenderer($renderer);
        $this->assertSame($renderer, $this->handler->getRenderer());
    }

    public function testEventIsCreatedFromExceptionAndPassedToRendererAndExitSignalIsReturned()
    {
        $exception = new ErrorException("Message", 1234, E_USER_WARNING);

        $this->mockRenderer(function (Event $event) use ($exception) {
            $this->assertSame(Level::severityToLevel($exception->getSeverity()), $event->getLevel());

            $exceptions = $event->getExceptions();

            $this->assertEquals(1, count($exceptions));
            $this->assertEquals($exception->getMessage(), $exceptions[0]->getMessage());
            $this->assertEquals(ErrorException::class, $exceptions[0]->getType());
        });

        $this->assertEquals(EventHandler::SIGNAL_EXIT, $this->handleException($exception));
    }

    public function testNestedExceptionsArePassedToEvent()
    {
        $innerException = new Exception("Inner exception message", 1234);
        $outerException = new RuntimeException("Outer exception message", 5678, $innerException);

        $this->mockRenderer(function (Event $event) use ($outerException, $innerException) {
            $exceptions = $event->getExceptions();

            $this->assertEquals($outerException->getMessage(), $exceptions[0]->getMessage());
            $this->assertEquals(RuntimeException::class, $exceptions[0]->getType());

            $this->assertEquals($innerException->getMessage(), $exceptions[1]->getMessage());
            $this->assertEquals(Exception::class, $exceptions[1]->getType());
        });

        $this->handleException($outerException);
    }

    public function testEventsHaveContext()
    {
        $this->mockRenderer(function (Event $event) {
            $this->assertInstanceOf(EventContext::class, $event->getContext());
        });
        $this->handleException();
    }

    public function testWhenWebRequest_eventContextHasHTTPRequestInstance()
    {
        $request = $this->createMock(Request::class);
        $this->system->setIsWeb($request);

        $this->mockRenderer(function (Event $event) use ($request) {
            $this->assertSame($request, $event->getContext()->getHTTPRequest());
        });

        $this->handleException();
    }

    public function testReleaseIsPassedToContext()
    {
        $release = "1.2.3.4";

        $this->handler->setRelease($release);

        $this->mockRenderer(function (Event $event) use ($release) {
            $this->assertEquals($release, $event->getContext()->getRelease());
        });

        $this->handleException();
    }

    public function testServerDataIsPassedToContext()
    {
        $data = ["name" => "value"];

        $this->system->setServerData($data);

        $this->mockRenderer(function (Event $event) use ($data) {
            $this->assertEquals($data, $event->getContext()->getServer());
        });

        $this->handleException();
    }

    public function testUserDataIsPassedToContext()
    {
        $user = new User();
        $user->setId(5);

        $this->handler->setUser($user);

        $this->mockRenderer(function (Event $event) use ($user) {
            $this->assertSame($user, $event->getContext()->getUser());
        });

        $this->handleException();
    }

    public function testCanAddBreadcrumbs()
    {
        $crumbTitle = "Something happened";
        $crumbData = ["foo" => "bar"];
        $this->handler->recordBreadcrumb($crumbTitle, $crumbData);

        $this->mockRenderer(function (Event $event) use ($crumbTitle, $crumbData) {
            $context = $event->getContext();

            $breadcrumb = $context->getBreadcrumbs()->getCrumbs()[0];
            $this->assertEquals($crumbTitle, $breadcrumb->getTitle());
            $this->assertEquals($crumbData, $breadcrumb->getData());
        });

        $this->handleException();
    }

    public function testApplicationPath()
    {
        $path = "/var/www/example";
        $this->handler->setApplicationPath($path);

        $this->mockRenderer(function (Event $event) use ($path) {
            $this->assertEquals($path, $event->getContext()->getApplicationPath());
        });

        $this->handleException();
    }
}