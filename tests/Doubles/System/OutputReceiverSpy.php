<?php

namespace SlashTrace\Tests\Doubles\System;

use SlashTrace\System\OutputReceiver;

class OutputReceiverSpy implements OutputReceiver
{
    /** @var string[] */
    private $output = [];

    public function output($string)
    {
        $this->output[] = $string;
    }

    /**
     * @return string[]
     */
    public function getOutput()
    {
        return $this->output;
    }
}