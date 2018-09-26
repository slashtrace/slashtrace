<?php

namespace SlashTrace\Tests\Fixtures;

use SlashTrace\Context\EventContext;
use SlashTrace\Context\User;
use SlashTrace\Event;
use SlashTrace\Exception\ExceptionData;
use SlashTrace\Exception\ExceptionInspector;
use SlashTrace\Level;
use SlashTrace\Tests\Doubles\Context\MockRequest;
use SlashTrace\Tests\Doubles\StackTrace\MaxDepthStackTraceInspector;
use SlashTrace\Tests\Fixtures\Code\TestClass;
use DateTime;
use Exception;

class EventProvider
{
    /**
     * @return Event
     * @throws Exception
     */
    public function createEvent()
    {
        $event = new Event();
        $event->setLevel(Level::ERROR);

        foreach ($this->createExceptions() as $exception) {
            $event->addException($exception);
        }

        $event->setContext($this->createEventContext());

        return $event;
    }

    /**
     * @return ExceptionData[]
     */
    private function createExceptions()
    {
        $exceptions = [];

        try {
            $testObject = new TestClass();
            $testObject->initialize();

        } catch (Exception $exception) {
            $inspector = new ExceptionInspector();
            $inspector->setStackTraceInspector(new MaxDepthStackTraceInspector(10));

            do {
                $exceptions[] = $inspector->inspect($exception);
            } while ($exception = $exception->getPrevious());
        }

        return $exceptions;
    }

    /**
     * @return EventContext
     * @throws Exception
     */
    private function createEventContext()
    {
        $context = new EventContext();

        $context->setUser($this->createUser());
        $context->setServer($this->createServerData());
        $context->setHttpRequest($this->createHttpRequest());
        $context->setApplicationPath($this->getApplicationPath());
        $context->setBreadcrumbs($this->createBreadcrumbs());

        return $context;
    }

    /**
     * @return User
     */
    private function createUser()
    {
        $user = new User();
        $user->setName("John Doe");
        $user->setEmail("john.doe@example.com");
        $user->setId(5);

        return $user;
    }

    private function createServerData()
    {
        return [
            'SCRIPT_NAME' => '/lorem/ipsum/index.php',
            'REQUEST_URI' => '/lorem/ipsum/index.php',
            'QUERY_STRING' => '',
            'REQUEST_METHOD' => 'GET',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'REMOTE_PORT' => '60894',
            'SCRIPT_FILENAME' => '/var/www/lorem/ipsum/index.php',
            'SERVER_ADMIN' => '[no address given]',
            'CONTEXT_DOCUMENT_ROOT' => '/var/www',
            'CONTEXT_PREFIX' => '',
            'REQUEST_SCHEME' => 'http',
            'DOCUMENT_ROOT' => '/var/www',
            'REMOTE_ADDR' => '192.168.100.1',
            'SERVER_PORT' => '80',
            'SERVER_ADDR' => '192.168.100.100',
            'SERVER_NAME' => 'example.com',
            'SERVER_SOFTWARE' => 'Apache/2.4.23 (Ubuntu)',
            'SERVER_SIGNATURE' => '',
            'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
            'HTTP_CACHE_CONTROL' => 'no-cache',
            'HTTP_PRAGMA' => 'no-cache',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.5',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:53.0) Gecko/20100101 Firefox/53.0',
            'HTTP_HOST' => 'example.com',
            'HTTP_AUTHORIZATION' => '',
            'FCGI_ROLE' => 'RESPONDER',
            'PHP_SELF' => '/var/www/lorem/ipsum/index.php'
        ];
    }

    private function createHttpRequest()
    {
        $request = new MockRequest();

        $request->setHeaders([
            'Accept-Encoding' => 'gzip, deflate',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Cache-Control' => 'max-age=0',
            'Cookie' => 'G_ENABLED_IDPS=google',
            'Connection' => 'keep-alive',
            'Host' => 'example.com',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Upgrade-Insecure-Requests' => '1',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:53.0) Gecko/20100101 Firefox/53.0',
            'Dnt' => '1',
        ]);

        $request->setGetData([
            "controller" => "test",
            "action" => "demo",
            "category_id" => 100
        ]);

        $request->setCookies([
            "PHPSESSID" => "4i5kreh906u63dmefo5imiueo5",
            "language" => "en_US"
        ]);

        $request->setURL("https://example.com");
        $request->setIP("192.168.100.100");

        return $request;
    }

    private function getApplicationPath()
    {
        return dirname(dirname(dirname(__FILE__)));
    }

    private function createBreadcrumbs()
    {
        $crumbs = new BreadcrumbsFixture();
        $crumbs->setTime(new DateTime("2017-05-02 19:16:30"));

        $crumbs->record("Application started", [
            "hostname" => "example.com",
            "stage" => "production"
        ]);

        $crumbs->record("User is logged in", [
            "email" => "john.doe@example.com",
            "role" => "administrator"
        ]);

        $crumbs->record("Article saved", [
            "id" => 1234,
            "title" => "The quick brown fox jumps over the lazy dog"
        ]);

        return $crumbs;
    }
}