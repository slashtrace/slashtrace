<?php

namespace SlashTrace\Context\Breadcrumbs;

use DateTime;

class Breadcrumb
{
    /** @var string */
    private $title;

    /** @var array */
    private $data = [];

    /** @var DateTime */
    private $dateTime;

    /**
     * @param string $title
     * @param array $data
     * @param DateTime $dateTime
     */
    public function __construct($title, array $data, DateTime $dateTime)
    {
        $this->title = $title;
        $this->dateTime = $dateTime;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }
}