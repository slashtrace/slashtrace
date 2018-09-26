<?php

namespace SlashTrace\System;

use League\CLImate\CLImate;

class CLImateOutput implements OutputReceiver
{
    /** @var CLImate */
    private $climate;

    public function __construct(CLImate $climate)
    {
        $this->climate = $climate;
    }

    public function output($string)
    {
        $this->climate->out($string);
    }
}