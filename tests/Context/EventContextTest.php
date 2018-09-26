<?php

namespace SlashTrace\Tests\Context;

use SlashTrace\Context\Breadcrumbs;
use SlashTrace\Context\EventContext;
use SlashTrace\Context\User;

use SlashTrace\Tests\Doubles\System\MockSystemProvider;
use SlashTrace\Tests\TestCase;

use Exception;

class EventContextTest extends TestCase
{
    /** @var EventContext */
    private $context;

    protected function setUp()
    {
        parent::setUp();
        $this->context = new EventContext();
    }

    public function testWhenNew_hasCustomDataIsFalse()
    {
        $this->assertFalse($this->context->hasCustomData());
    }

    public function testNoCustomData()
    {
        $this->context->setHttpRequest(new \SlashTrace\Http\Request());
        $this->context->setServer(["name" => "value"]);
        $this->assertFalse($this->context->hasCustomData());
    }

    public function testWhenContextHasEmptyBreadcrumbs_customDataIsFalse()
    {
        $this->context->setBreadcrumbs(new Breadcrumbs(new MockSystemProvider()));
        $this->assertFalse($this->context->hasCustomData());
    }

    public function testWhenContextHasBreadcrumbs_customDataIsTrue()
    {
        $breadcrumbs = new Breadcrumbs(new MockSystemProvider());
        $breadcrumbs->record("Test");

        $this->context->setBreadcrumbs($breadcrumbs);
        $this->assertTrue($this->context->hasCustomData());
    }

    public function testWhenContextHasRelease_customDataIsTrue()
    {
        $this->context->setRelease("1.2.3");
        $this->assertTrue($this->context->hasCustomData());
    }

    public function testWhenContextHasUser_customDataIsTrue()
    {
        $user = new User();
        $user->setId(5);

        $this->context->setUser($user);
        $this->assertTrue($this->context->hasCustomData());
    }

    public function testApplicationPathUsesSlashes()
    {
        $this->context->setApplicationPath("\Windows\Specific\Directory\Path");
        $this->assertEquals("/Windows/Specific/Directory/Path", $this->context->getApplicationPath());
    }

    public function testUserMustHaveIDOrEmail()
    {
        $user = new User();
        $user->setName("John Doe");

        $this->expectException(Exception::class);
        $this->context->setUser($user);
    }
}