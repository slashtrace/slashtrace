<?php

namespace SlashTrace\Formatter;

use Symfony\Component\VarDumper\Dumper\CliDumper;

class TextVarDumper extends VarDumper
{
    /**
     * @return CliDumper
     */
    protected function createDumper()
    {
        $dumper = new CliDumper();
        $dumper->setColors(false);
        return $dumper;
    }
}