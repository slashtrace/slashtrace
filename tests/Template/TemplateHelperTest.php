<?php

namespace SlashTrace\Tests\Template;

use SlashTrace\Formatter\StackFrameCallHtmlFormatter;
use SlashTrace\StackTrace\StackFrame;
use SlashTrace\Template\TemplateHelper;
use SlashTrace\Formatter\VarDumper;
use SlashTrace\Tests\TestCase;
use stdClass;

class TemplateHelperTest extends TestCase
{
    /** @var TemplateHelper */
    private $helper;

    /** @var StackFrame */
    private $frame;

    protected function setUp()
    {
        parent::setUp();

        $this->helper = new TemplateHelper();
        $this->frame = new StackFrame();
    }

    private function assertFormatContext($expected)
    {
        $this->assertEquals(
            $expected,
            $this->helper->formatStackFrameContext($this->frame)
        );
    }

    public function testWhenNoFrameContextLines_contextIsEmpty()
    {
        $this->assertFormatContext("");
    }

    public function testFrameContext()
    {
        $this->frame->setContext([
            1 => "Line 1",
            2 => "Line 2",
            3 => "Line 3"
        ]);
        $this->assertFormatContext("<pre>Line 1\nLine 2\nLine 3</pre>");
    }

    public function testFrameContextLineNumbers()
    {
        $this->frame->setContext([
            10 => "Line 1",
            11 => "Line 2",
            12 => "Line 3"
        ]);

        $this->assertFormatContext("<pre data-start=\"10\">Line 1\nLine 2\nLine 3</pre>");
    }

    public function testFrameActiveLine()
    {
        $this->frame->setContext([
            10 => "Line 1",
            11 => "Line 2",
            12 => "Line 3"
        ]);
        $this->frame->setLine(11);

        $this->assertFormatContext("<pre data-start=\"10\" data-line=\"11\">Line 1\nLine 2\nLine 3</pre>");
    }

    public function testHTMLEntitiesAreEscaped()
    {
        $this->frame->setContext([
            1 => '<?php',
            2 => '$a = new A();',
            3 => '$b = implode("\n", [1, 2, 3]);'
        ]);
        $this->assertFormatContext("<pre>&lt;?php\n\$a = new A();\n\$b = implode(&quot;\\n&quot;, [1, 2, 3]);</pre>");
    }

    /**
     * Prettyprint drops empty lines from beginning of <pre> tags, so we
     * must replace them with spaces
     */
    public function testEmptyLinesAreReplacedWithSpace()
    {
        $this->frame->setContext([
            1 => "",
            2 => "Line 2",
            3 => "Line 3"
        ]);
        $this->assertFormatContext("<pre> \nLine 2\nLine 3</pre>");
    }

    public function testWhenNoVarDumperSet_defaultIsReturned()
    {
        $this->assertInstanceOf(VarDumper::class, $this->helper->getVarDumper());
    }

    public function testCanSetVarDumper()
    {
        $dumper = $this->createMock(VarDumper::class);
        /** @noinspection PhpParamsInspection */
        $this->helper->setVarDumper($dumper);
        $this->assertSame($dumper, $this->helper->getVarDumper());
    }

    public function testDumpArgumentsArePassedToDumper()
    {
        $argument = new stdClass();

        $dumper = $this->createMock(VarDumper::class);
        $dumper->expects($this->once())->method("dump")->with($argument);

        /** @noinspection PhpParamsInspection */
        $this->helper->setVarDumper($dumper);
        $this->helper->dump($argument);
    }

    public function testStackFrameCallHTMLFormatterIsUsedToFormatStackFrameCall()
    {
        $this->frame->setClassName("TestClass");
        $this->frame->setFunctionName("test");
        $this->frame->setArguments(["/var/www/index.php", new stdClass()]);

        $formatter = new StackFrameCallHtmlFormatter();
        $this->assertEquals(
            $formatter->format($this->frame),
            $this->helper->formatStackFrameCall($this->frame)
        );
    }

}