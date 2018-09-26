<?php

namespace SlashTrace\Tests\StackTrace;

use SlashTrace\StackTrace\StackFrame;
use SlashTrace\Tests\TestCase;

class StackFrameTest extends TestCase
{

    /** @var StackFrame */
    private $frame;

    protected function setUp()
    {
        parent::setUp();
        $this->frame = new StackFrame();
    }

    public function testWhenNoApplicationPath_relativePathIsSameAsFile()
    {
        $file = "/var/www/test/test.php";
        $this->frame->setFile($file);
        $this->assertEquals($file, $this->frame->getRelativeFile(null));
    }

    public function testWhenApplicationPathDoesNotMatch_relativePathIsTheSameAsFile()
    {
        $file = "/var/www/test/test.php";
        $this->frame->setFile($file);
        $this->assertEquals($file, $this->frame->getRelativeFile("/lorem/ipsum"));
    }

    public function testApplicationPathIsReplacedInRelativeFile()
    {
        $this->frame->setFile("/var/www/test/test.php");

        $this->assertEquals("test/test.php", $this->frame->getRelativeFile("/var/www"));
        $this->assertEquals("test/test.php", $this->frame->getRelativeFile("/var/www/"));
    }

    public function testFilePathUsesSlashes()
    {
        $this->frame->setFile("\Windows\Specific\File\path.php");
        $this->assertEquals("/Windows/Specific/File/path.php", $this->frame->getFile());
    }

}