<?php

namespace SlashTrace\Tests\Formatter;

use SlashTrace\Formatter\StackTraceTagFormatter;
use SlashTrace\Tests\TestCase;

/**
 * @covers \SlashTrace\Formatter\StackTraceTagFormatter
 */
class StackTraceTagFormatterTest extends TestCase
{
    /** @var StackTraceTagFormatter */
    private $formatter;

    protected function setUp()
    {
        parent::setUp();
        $this->formatter = new StackTraceTagFormatter();
    }

    public function testWhenNoTagsAreConfigured_outputIsTheSame()
    {
        $this->assertEquals("test", $this->formatter->format("test", "tag"));
    }

    public function testConfiguredTagsAreReplaced()
    {
        $this->formatter->setTags([
            StackTraceTagFormatter::TAG_ARGUMENT => "test"
        ]);
        $this->assertEquals("<test>test</test>", $this->formatter->format("test", StackTraceTagFormatter::TAG_ARGUMENT));
    }

    public function testCanSetTagsByKeyOnly()
    {
        $this->formatter->setTags([StackTraceTagFormatter::TAG_TYPE]);
        $this->assertEquals("<type>test</type>", $this->formatter->format("test", StackTraceTagFormatter::TAG_TYPE));
    }
}