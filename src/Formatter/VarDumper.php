<?php

namespace SlashTrace\Formatter;

use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\AbstractDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class VarDumper
{
    /** @var AbstractDumper */
    private $dumper;

    /** @var VarCloner */
    private $cloner;

    /** @var callable */
    private $outputCallback;

    private $output = "";

    /**
     * @param mixed $argument
     * @return string
     */
    public function dump($argument)
    {
        $this->output = "";
        $this->getDumper()->dump(
            $this->getCloner()->cloneVar($argument, Caster::EXCLUDE_VERBOSE),
            $this->getOutputCallback()
        );
        return trim($this->output);
    }

    /**
     * @return AbstractDumper
     */
    protected function getDumper()
    {
        if (is_null($this->dumper)) {
            $this->dumper = $this->createDumper();
        }
        return $this->dumper;
    }

    protected function createDumper()
    {
        return new HtmlDumper();
    }

    private function getCloner()
    {
        if (is_null($this->cloner)) {
            $this->cloner = new VarCloner();
            $this->cloner->setMaxItems(50);
        }
        return $this->cloner;
    }

    /**
     * Use a custom output callback for the Symfony VarDumper component, to prevent the HtmlDumper header (JS and CSS)
     * from being generated with each dump
     *
     * @see http://symfony.com/doc/current/components/var_dumper/advanced.html#dumpers
     * @return callable
     */
    private function getOutputCallback()
    {
        if (is_null($this->outputCallback)) {
            $this->outputCallback = function ($line, $depth) {
                if ($depth < 0) {
                    return;
                }
                $indent = str_repeat("  ", $depth);
                $this->output .= $indent . $line . "\n";
            };
        }
        return $this->outputCallback;
    }
}