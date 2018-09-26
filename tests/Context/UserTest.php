<?php

namespace SlashTrace\Tests\Context;

use SlashTrace\Context\User;
use SlashTrace\Tests\TestCase;

use InvalidArgumentException;

class UserTest extends TestCase
{

    /** @var User */
    private $user;

    protected function setUp()
    {
        parent::setUp();
        $this->user = new User();
    }

    public function testEmailIsValidated()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->user->setEmail("test");
    }

}
