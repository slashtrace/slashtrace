<?php

namespace SlashTrace\Tests\Context;

use SlashTrace\Http\Request;
use SlashTrace\Tests\TestCase;

/**
 * @covers \SlashTrace\Http\Request
 */
class RequestTest extends TestCase
{
    /** @var Request */
    private $request;

    protected function setUp()
    {
        parent::setUp();
        $this->request = new Request();
    }

    public function testHeaderNames()
    {
        $this->assertEquals("Accept", $this->request->getHeaderName("HTTP_ACCEPT"));
        $this->assertEquals("Accept-Language", $this->request->getHeaderName("HTTP_ACCEPT_LANGUAGE"));
        $this->assertEquals("Content-Type", $this->request->getHeaderName("CONTENT_TYPE"));
        $this->assertEquals("Content-Length", $this->request->getHeaderName("CONTENT_LENGTH"));
        $this->assertEquals("Content-Md5", $this->request->getHeaderName("CONTENT_MD5"));
    }

    public function testIgnoredServerKeys()
    {
        $this->assertNull($this->request->getHeaderName("REQUEST_URI"));
        $this->assertNull($this->request->getHeaderName("REMOTE_HOST"));
    }
}