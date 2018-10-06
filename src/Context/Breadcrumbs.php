<?php

namespace SlashTrace\Context;

use JsonSerializable;
use SlashTrace\Context\Breadcrumbs\Breadcrumb;
use SlashTrace\System\SystemProvider;
use DateTime;

class Breadcrumbs implements JsonSerializable
{
    const MAX_SIZE = 25;

    /** @var SystemProvider */
    private $system;

    /** @var Breadcrumb[] */
    private $crumbs = [];

    public function __construct(SystemProvider $system)
    {
        $this->system = $system;
    }

    /**
     * @param string $title
     * @param array $data
     */
    public function record($title, array $data = [])
    {
        $breadcrumb = new Breadcrumb($title, $data, $this->getDateTime());

        if ($this->getSize() == self::MAX_SIZE) {
            array_shift($this->crumbs);
        }

        $this->crumbs[] = $breadcrumb;
    }

    /**
     * @return Breadcrumb[]
     */
    public function getCrumbs()
    {
        return $this->crumbs;
    }

    /**
     * @return DateTime
     */
    protected function getDateTime()
    {
        return $this->system->getDateTime();
    }

    /**
     * @return int
     */
    public function getMaxSize()
    {
        return self::MAX_SIZE;
    }

    /**
     * @return int
     */
    private function getSize()
    {
        return count($this->crumbs);
    }

    public function isEmpty()
    {
        return $this->getSize() == 0;
    }

    public function clear()
    {
        $this->crumbs = [];
    }

    public function jsonSerialize()
    {
        return $this->crumbs;
    }
}