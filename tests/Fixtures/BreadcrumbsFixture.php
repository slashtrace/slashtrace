<?php

namespace SlashTrace\Tests\Fixtures;

use SlashTrace\Context\Breadcrumbs;
use DateInterval;
use DateTime;
use SlashTrace\System\System;

class BreadcrumbsFixture extends Breadcrumbs
{
    /** @var DateTime */
    private $dateTime;

    public function __construct()
    {
        parent::__construct(System::getInstance());
    }

    protected function getDateTime()
    {
        $this->dateTime->add(new DateInterval("PT30S"));
        return clone $this->dateTime;
    }

    public function setTime(DateTime $time)
    {
        $this->dateTime = clone $time;
    }
}