<?php

namespace SlashTrace\Tests\Fixtures\Code;

use DateTime;
use ErrorException;
use LogicException;
use stdClass;

class TestClass
{
    /** @var stdClass */
    private $parameter;

    private static function bar(TestClass $object, array $countries = [])
    {
        assert(is_object($object));
        assert(is_array($countries));

        throw new ErrorException("Something went wrong!", 1234, E_USER_ERROR, __FILE__, __LINE__, self::createPreviousException());
    }

    private static function createPreviousException()
    {
        return new LogicException();
    }

    public function __construct(stdClass $parameter = null)
    {
        $this->parameter = $parameter;
    }

    public function initialize()
    {
        include __DIR__ . "/test_file.php";
    }

    public function doSomething(DateTime $when)
    {
        assert($when instanceof DateTime);
        $this->foo(new self, [
            "Finland", "Denmark", "Sweden"
        ]);
    }

    private function foo(TestClass $object, array $countries = [])
    {
        self::bar($object, $countries);
    }
}