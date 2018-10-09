<?php

namespace SlashTrace\Tests;

use SlashTrace\Context\EventContext;
use SlashTrace\Context\User;
use SlashTrace\Event;
use SlashTrace\Exception\ExceptionData;
use SlashTrace\Level;
use SlashTrace\StackTrace\StackFrame;

use SlashTrace\Tests\Doubles\Context\MockRequest;
use SlashTrace\Tests\Fixtures\BreadcrumbsFixture;

use DateTime;
use ErrorException;

/**
 * @covers \SlashTrace\Event
 */
class EventTest extends TestCase
{
    /**
     * @covers \SlashTrace\Event::jsonSerialize
     * @covers \SlashTrace\Exception\ExceptionData::jsonSerialize
     * @covers \SlashTrace\StackTrace\StackFrame::jsonSerialize
     * @covers \SlashTrace\Context\EventContext::jsonSerialize
     * @covers \SlashTrace\Http\Request::jsonSerialize
     * @covers \SlashTrace\Context\Breadcrumbs::jsonSerialize
     * @covers \SlashTrace\Context\Breadcrumbs\Breadcrumb::jsonSerialize
     * @covers \SlashTrace\Context\User::jsonSerialize
     */
    public function testJsonSerialize()
    {
        $event = new Event();
        $event->setLevel(Level::ERROR);
        $event->addException($this->mockException());
        $event->setContext($this->mockContext());

        $this->assertEquals(
            file_get_contents(__DIR__ . "/Fixtures/resources/event.json"),
            json_encode($event, JSON_PRETTY_PRINT)
        );
    }

    private function mockException()
    {
        $exception = new ExceptionData();
        $exception->setType(ErrorException::class);
        $exception->setMessage("Something went wrong!");

        $exception->setStackTrace([$this->mockStackFrane()]);
        return $exception;
    }

    private function mockStackFrane()
    {
        $frame = new StackFrame();
        $frame->setFile("/foo/bar.php");
        $frame->setLine(100);
        $frame->setClassName("SlashTrace\\Test");
        $frame->setFunctionName("test");
        $frame->setType(StackFrame::TYPE_METHOD);
        $frame->setArguments([1, 2, 3]);

        return $frame;
    }

    private function mockContext()
    {
        $context = new EventContext();

        $context->setHttpRequest($this->mockRequest());
        $context->setServer(["foo" => "bar"]);
        $context->setBreadcrumbs($this->mockBreadcrumbs());
        $context->setApplicationPath("/var/www");
        $context->setRelease("1.0.0");
        $context->setUser($this->mockUser());

        return $context;
    }

    private function mockRequest()
    {
        $request = new MockRequest();
        $request->setURL("https://example.com");
        $request->setHeaders(["Accept" => "application/json"]);
        $request->setCookies(["PHPSESSID" => "foo"]);
        $request->setPostData(["foo" => "bar"]);
        $request->setIP("123.123.123.123");

        return $request;
    }

    private function mockBreadcrumbs()
    {
        $breadcrumbs = new BreadcrumbsFixture();
        $breadcrumbs->setTime(new DateTime("2018-10-06T13:18:30+00:00"));
        $breadcrumbs->record("Foo", ["foo" => "bar"]);

        return $breadcrumbs;
    }

    private function mockUser()
    {
        $user = new User();
        $user->setId(123);
        $user->setName("John Doe");
        $user->setEmail("john.doe@example.com");

        return $user;
    }
}
