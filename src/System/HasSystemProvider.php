<?php

namespace SlashTrace\System;

trait HasSystemProvider
{
    /** @var SystemProvider */
    private $system;

    protected function getSystem()
    {
        if (is_null($this->system)) {
            $this->system = System::getInstance();
        }
        return $this->system;
    }

    public function setSystem(SystemProvider $system)
    {
        $this->system = $system;
    }
}