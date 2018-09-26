<?php

namespace SlashTrace\Tests\Serializer;

use SlashTrace\Serializer\HumanSerializer;
use SlashTrace\Tests\TestCase;
use stdClass;

class HumanSerializerTest extends TestCase
{

    /** @var HumanSerializer */
    private $serializer;

    protected function setUp()
    {
        parent::setUp();
        $this->serializer = new HumanSerializer();
    }

    private function serialize($input)
    {
        return $this->serializer->serialize($input);
    }

    private function compare($expected, $input)
    {
        $this->assertEquals($expected, $this->serialize($input));
    }

    public function testNull()
    {
        $this->compare("null", null);
    }

    public function testBoolean()
    {
        $this->compare("true", true);
        $this->compare("false", false);
    }

    public function testNumbers()
    {
        $this->compare("5", 5);
        $this->compare("-5", -5);
        $this->compare("5.0", 5.0);
        $this->compare("-5.75", -5.75);
    }

    public function testEmptyArray()
    {
        $this->compare("array[0]", []);
    }

    public function testArray()
    {
        $this->compare("array[3]", [1, 2, 3]);
    }

    public function testStdClass()
    {
        $this->compare("Object[stdClass]", new stdClass());
    }

    public function testObject()
    {
        $this->compare("Object[" . HumanSerializer::class . "]", new HumanSerializer());
    }

    public function testClosure()
    {
        $this->compare("Object[Closure]", function () {});
    }

    public function testString()
    {
        $this->compare('"Lorem ipsum"', "Lorem ipsum");
    }

    public function testMaximumStringLength()
    {
        $this->assertEquals('"12345[...]"', $this->serializer->serialize("1234567890", 5));
    }

}
