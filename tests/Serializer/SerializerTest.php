<?php

namespace SlashTrace\Tests\Serializer;

use SlashTrace\Serializer\Serializer;
use SlashTrace\Tests\TestCase;

use DateTime;
use stdClass;

class SerializerTest extends TestCase
{

    /** @var Serializer */
    private $serializer;

    protected function setUp()
    {
        parent::setUp();
        $this->serializer = new Serializer();
    }

    private function compare($expected, $input)
    {
        $this->assertSame($expected, $this->serialize($input));
    }

    private function serialize($input)
    {
        return $this->serializer->serialize($input);
    }

    public function testNull()
    {
        $this->compare(null, null);
    }

    public function testEmptyString()
    {
        $this->compare("", "");
    }

    public function testString()
    {
        $this->compare("test", "test");
    }

    public function testMaxStringLength()
    {
        $maxLength = $this->serializer->getMaxLength();
        $string = str_repeat("x", $maxLength + 1);
        $this->compare(substr($string, 0, $maxLength) . "[...]", $string);
    }

    public function testBooleans()
    {
        $this->compare(true, true);
        $this->compare(false, false);
    }

    public function testNumbers()
    {
        $this->compare(1, 1);
        $this->compare(-1, -1);
        $this->compare(1.5, 1.5);
        $this->compare(-1.5, -1.5);
    }

    public function testEmptyArray()
    {
        $this->compare([], []);
    }

    public function testArray()
    {
        $array = ["name" => "John Doe", "age" => 32];
        $this->compare($array, $array);
    }

    public function testNestedArray()
    {
        $array = [
            "name" => "John Doe",
            "languages" => ["English", "Swahili"]
        ];
        $this->compare($array, $array);
    }

    public function testMaximumArrayDepth()
    {
        $array = [
            "foo" => [
                "bar" => [
                    "baz" => [1, 2, 3]
                ]
            ]
        ];
        $this->assertEquals("array[3]", $this->serialize($array)["foo"]["bar"]["baz"]);
    }

    public function testMaximumArrayKeyLength()
    {
        $maxLength = $this->serializer->getMaxLength();
        $string = str_repeat("x", $maxLength + 1);

        $this->compare(
            [substr($string, 0, $maxLength) . "[...]" => "foo"],
            [$string => "foo"]
        );
    }

    public function testStdClass()
    {
        $object = new stdClass;
        $object->name = "John Doe";
        $object->age = 32;

        $this->compare(
            ["name" => $object->name, "age" => $object->age],
            $object
        );
    }

    public function testNestedStdClasses()
    {
        $foo = new stdClass;
        $bar = new stdClass;
        $bar->baz = "foobarbaz";
        $foo->bar = $bar;

        $this->compare(["bar" => ["baz" => "foobarbaz"]], $foo);
    }

    public function testObject()
    {
        $this->compare("Object[" . Serializer::class . "]", new Serializer());
    }

    public function testClosure()
    {
        $this->compare("Object[Closure]", function () {
        });
    }

    public function testResource()
    {
        $resource = fopen(__FILE__, "r");
        $this->compare("resource[stream]", $resource);
        fclose($resource);
    }

    public function testToStringAcceptsMaxLength()
    {
        $this->assertEquals("12345[...]", $this->serializer->toString("1234567890", 5));
    }

    public function testDateTime()
    {
        $date = new DateTime();
        $formatted = $date->format(DATE_ATOM);

        $this->compare("DateTime[$formatted]", $date);
    }

}