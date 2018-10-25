<?php

namespace SlashTrace\Tests;

use SlashTrace\Context\User;
use SlashTrace\EventHandler\EventHandler;
use SlashTrace\EventHandler\EventHandlerException;
use SlashTrace\SlashTrace;

use SlashTrace\Tests\Doubles\System\MockSystemProvider;

use PHPUnit\Framework\MockObject\MockObject;
use ErrorException;
use Exception;
use RuntimeException;

/**
 * @covers \SlashTrace\SlashTrace
 */
class SlashTraceTest extends TestCase
{
    /** @var SlashTrace */
    private $slashtrace;

    /** @var MockSystemProvider */
    private $system;

    protected function setUp()
    {
        $this->system = new MockSystemProvider();
        $this->slashtrace = new SlashTrace();
        $this->slashtrace->setSystem($this->system);
    }

    /**
     * @param callable|null $handleCallback
     * @return MockObject|EventHandler
     */
    private function mockHandler(callable $handleCallback = null)
    {
        $handler = $this->createMock(EventHandler::class);

        if (!is_null($handleCallback)) {
            $handler->expects($this->once())
                ->method("handleException")
                ->willReturnCallback($handleCallback);
        }

        /** @noinspection PhpParamsInspection */
        $this->slashtrace->addHandler($handler);

        return $handler;
    }

    private function handleException(Exception $e = null)
    {
        return $this->slashtrace->handleException($e ?: new Exception());
    }

    public function testNoHandlersByDefault()
    {
        $this->assertEmpty($this->slashtrace->getHandlers());
    }

    public function testCanAddHandlers()
    {
        $handler1 = $this->createMock(EventHandler::class);
        $handler2 = $this->createMock(EventHandler::class);

        /** @noinspection PhpParamsInspection */
        $this->slashtrace->addHandler($handler1);
        $this->slashtrace->addHandler($handler2);

        $handlers = $this->slashtrace->getHandlers();

        $this->assertEquals(2, count($handlers));
        $this->assertSame($handler1, $handlers[0]);
        $this->assertSame($handler2, $handlers[1]);
    }

    public function testCanPushHandlers()
    {
        $handler1 = $this->createMock(EventHandler::class);
        $handler2 = $this->createMock(EventHandler::class);

        /** @noinspection PhpParamsInspection */
        $this->slashtrace->pushHandler($handler1);
        $this->slashtrace->pushHandler($handler2);

        $handlers = $this->slashtrace->getHandlers();

        $this->assertEquals(2, count($handlers));
        $this->assertSame($handler2, $handlers[0]);
        $this->assertSame($handler1, $handlers[1]);
    }

    public function testAddingTheSameHandlerTwiceRaisesException()
    {
        $handler = $this->createMock(EventHandler::class);
        $service = new SlashTrace();

        /** @noinspection PhpParamsInspection */
        $service->addHandler($handler);

        $this->expectException(RuntimeException::class);

        /** @noinspection PhpParamsInspection */
        $service->addHandler($handler);
    }

    public function testCanAddTheSameTypeOfHandlerMultipleTimes()
    {
        $service = new SlashTrace();

        /** @noinspection PhpParamsInspection */
        $service->addHandler($this->createMock(EventHandler::class));

        /** @noinspection PhpParamsInspection */
        $service->addHandler($this->createMock(EventHandler::class));

        $this->assertEquals(2, count($service->getHandlers()));
    }

    public function testExceptionIsPassedToAllHandlers()
    {
        $exception = new ErrorException("Message", 1234, E_USER_WARNING);

        for ($i = 0; $i < 5; $i++) {
            $this->mockHandler(function (Exception $e) use ($exception) {
                $this->assertSame($exception, $e);
            });
        }

        $this->handleException($exception);
    }

    public function testUserIsPassedToAllHandlers()
    {
        $user = new User();

        for ($i = 0; $i < 5; $i++) {
            $handler = $this->mockHandler();
            $handler->expects($this->once())
                ->method("setUser")
                ->with($user);
        }

        $this->slashtrace->setUser($user);
    }

    public function testBreadcrumbsArePassedToAllHandlers()
    {
        $title = "Something happened!";
        $data = ["foo" => "bar"];

        for ($i = 0; $i < 5; $i++) {
            $handler = $this->mockHandler();
            $handler->expects($this->once())
                ->method("recordBreadcrumb")
                ->with($title, $data);
        }

        $this->slashtrace->recordBreadcrumb($title, $data);
    }

    public function testReleaseIsPassedToAllHandlers()
    {
        $release = "1.0.0";

        for ($i = 0; $i < 5; $i++) {
            $handler = $this->mockHandler();
            $handler->expects($this->once())
                ->method("setRelease")
                ->with($release);
        }

        $this->slashtrace->setRelease($release);
    }

    public function testApplicationPathIsPassedToAllHandlers()
    {
        $path = __DIR__;

        for ($i = 0; $i < 5; $i++) {
            $handler = $this->mockHandler();
            $handler->expects($this->once())
                ->method("setApplicationPath")
                ->with($path);
        }

        $this->slashtrace->setApplicationPath($path);
    }

    public function testContinueSignalIsReturned()
    {
        $this->mockHandler(function () {
            return EventHandler::SIGNAL_CONTINUE;
        });

        $this->mockHandler(function () {
            return EventHandler::SIGNAL_CONTINUE;
        });

        $this->assertEquals(EventHandler::SIGNAL_CONTINUE, $this->handleException());
    }

    public function testExitSignalIsReturned()
    {
        $this->mockHandler(function () {
            return EventHandler::SIGNAL_EXIT;
        });

        $handler = $this->createMock(EventHandler::class);
        $handler->expects($this->never())->method("handleException");

        /** @noinspection PhpParamsInspection */
        $this->slashtrace->addHandler($handler);

        $this->assertEquals(EventHandler::SIGNAL_EXIT, $this->handleException());
    }

    public function testHandlerErrorsAreLogged_andExitSignalIsReturned()
    {
        $handlerException = new EventHandlerException("Error message", 400);

        $this->mockHandler(function () use ($handlerException) {
            throw $handlerException;
        });

        $this->assertEquals(EventHandler::SIGNAL_EXIT, $this->handleException());

        $this->assertTrue($this->system->logErrorCalled);
        $this->assertEquals(
            "SlashTrace error (" . $handlerException->getCode() . "): " . $handlerException->getMessage(),
            $this->system->logErrorArgument
        );
    }

    public function testHandlerErrorsWithoutCode()
    {
        $handlerException = new EventHandlerException("Error message");

        $this->mockHandler(function () use ($handlerException) {
            throw $handlerException;
        });

        $this->handleException();

        $this->assertTrue($this->system->logErrorCalled);
        $this->assertEquals(
            "SlashTrace error: " . $handlerException->getMessage(),
            $this->system->logErrorArgument
        );
    }

    public function testInstallsHandlers()
    {
        $this->slashtrace->register();

        $this->assertTrue(is_callable($this->system->getErrorHandler()));
        $this->assertTrue(is_callable($this->system->getExceptionHandler()));
        $this->assertTrue(is_callable($this->system->getShutdownFunction()));
    }
}
