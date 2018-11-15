<?php

namespace SlashTrace\Tests\StackTrace;

use SlashTrace\StackTrace\StackFrameContextExtractor;
use SlashTrace\Tests\TestCase;
use InvalidArgumentException;
use RuntimeException;

class StackFrameContextExtractorTest extends TestCase
{

    /** @var StackFrameContextExtractor */
    private $extractor;

    protected function setUp()
    {
        parent::setUp();
        $this->extractor = new StackFrameContextExtractor();
    }

    private function getContext($line, $count)
    {
        return $this->extractor->getContext(__DIR__ . "/../Fixtures/resources/lines.txt", $line, $count);
    }

    public function testWhenFileDoesNotExist_exceptionIsThrown()
    {
        $this->expectException(RuntimeException::class);
        $this->extractor->getContext("/missing/file", 0, 5);
    }

    public function testWhenFileIsUnknownEmptyArrayIsReturned()
    {
        $this->assertEquals([], $this->extractor->getContext("Unknown", 0));
    }

    public function testWhenNegativeLineNumberProvided_exceptionIsThrown()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->getContext(-1, 5);
    }

    public function testLineNumberIsNotZeroBased()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->getContext(0, 5);
    }

    public function testWhenNegativeContextLinesCount_exceptionIsThrown()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->getContext(1, -1);
    }

    public function testCanGetSingleLine()
    {
        $context = $this->getContext(3, 0);
        $this->assertEquals([3 => "Line 3"], $context);
    }

    public function testFirstLines()
    {
        $this->assertEquals([
            1 => "Line 1",
            2 => "Line 2",
            3 => "Line 3",
            4 => "Line 4",
            5 => "Line 5",
            6 => "Line 6",
        ], $this->getContext(1, 5));
    }

    public function testLastLines()
    {
        $this->assertEquals([
            2 => "Line 2",
            3 => "Line 3",
            4 => "Line 4",
            5 => "Line 5",
            6 => "Line 6",
            7 => "Line 7",
            8 => "Line 8",
            9 => "Line 9",
            10 => "Line 10",
        ], $this->getContext(7, 5));
    }

}