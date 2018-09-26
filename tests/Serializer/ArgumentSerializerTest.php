<?php

namespace SlashTrace\Tests\Serializer;

use SlashTrace\Formatter\VarDumper;
use SlashTrace\Serializer\ArgumentSerializer;
use SlashTrace\Serializer\HumanSerializer;

use SlashTrace\Tests\TestCase;

class ArgumentSerializerTest extends TestCase
{

    /** @var ArgumentSerializer */
    private $serializer;

    protected function setUp()
    {
        parent::setUp();
        $this->serializer = new ArgumentSerializer();
    }

    public function testDefaultHumanSerializerIsUsed()
    {
        $this->assertInstanceOf(
            HumanSerializer::class,
            $this->serializer->getHumanSerializer()
        );
    }

    public function testCanSetHumanSerializer()
    {
        $humanSerializer = $this->createMock(HumanSerializer::class);
        /** @noinspection PhpParamsInspection */
        $this->serializer->setHumanSerializer($humanSerializer);
        $this->assertSame($humanSerializer, $this->serializer->getHumanSerializer());
    }

    public function testDefaultVarDumperIsUsed()
    {
        $this->assertInstanceOf(VarDumper::class, $this->serializer->getDumper());
    }

    public function testCanSetVarDumper()
    {
        $dumper = $this->createMock(VarDumper::class);
        /** @noinspection PhpParamsInspection */
        $this->serializer->setDumper($dumper);
        $this->assertSame($dumper, $this->serializer->getDumper());
    }

    public function testOutput()
    {
        $humanSerializer = $this->createMock(HumanSerializer::class);
        $humanSerializer->expects($this->once())
            ->method("serialize")
            ->with($this)
            ->willReturn("Foo");

        $dumper = $this->createMock(VarDumper::class);
        $dumper->expects($this->once())
            ->method("dump")
            ->with($this)
            ->willReturn("Bar");

        /** @noinspection PhpParamsInspection */
        $this->serializer->setHumanSerializer($humanSerializer);

        /** @noinspection PhpParamsInspection */
        $this->serializer->setDumper($dumper);

        $this->assertEquals([
            "type" => gettype($this),
            "repr" => "Foo",
            "dump" => "Bar"
        ], $this->serializer->serialize($this));
    }

}
