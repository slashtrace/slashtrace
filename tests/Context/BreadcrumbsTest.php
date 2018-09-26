<?php

namespace SlashTrace\Tests\Context;

use SlashTrace\Context\Breadcrumbs;
use SlashTrace\Context\Breadcrumbs\Breadcrumb;
use SlashTrace\Tests\Doubles\System\MockSystemProvider;
use SlashTrace\Tests\TestCase;
use DateTime;

class BreadcrumbsTest extends TestCase
{
    /** @var Breadcrumbs */
    private $crumbs;

    /** @var MockSystemProvider */
    private $system;

    protected function setUp()
    {
        parent::setUp();

        $this->system = new MockSystemProvider();
        $this->crumbs = new Breadcrumbs($this->system);
    }

    public function testInitiallyEmpty()
    {
        $this->assertEmpty($this->crumbs->getCrumbs());
    }

    public function testCanAddCrumbs()
    {
        $title = "Did something";
        $data = [
            "foo" => "bar",
            "bar" => "baz"
        ];

        $this->crumbs->record($title, $data);
        $this->crumbs->record("Something happened");

        $crumbs = $this->crumbs->getCrumbs();
        $this->assertEquals(2, count($crumbs));
        $this->assertInstanceOf(Breadcrumb::class, $crumbs[0]);

        $this->assertEquals($title, $crumbs[0]->getTitle());
        $this->assertEquals($data, $crumbs[0]->getData());
    }

    public function testCrumbsHaveTimestamp()
    {
        $dateTime = new DateTime();

        $this->system->setDateTime($dateTime);

        $this->crumbs->record("Something happened");
        $crumb = $this->crumbs->getCrumbs()[0];
        $this->assertEquals($dateTime->getTimestamp(), $crumb->getDateTime()->getTimestamp());
    }

    public function testMaximumCrumbCount()
    {
        $maxSize = $this->crumbs->getMaxSize();

        foreach (range(1, $maxSize) as $i) {
            $this->crumbs->record("Breadcrumb $i");
        }

        $this->crumbs->record("Last breadcrumb");

        $breadcrumbs = $this->crumbs->getCrumbs();
        $this->assertEquals($maxSize, count($breadcrumbs));
        $this->assertEquals("Breadcrumb 2", $breadcrumbs[0]->getTitle());
        $this->assertEquals("Last breadcrumb", $breadcrumbs[$maxSize - 1]->getTitle());
    }

    public function testCanClearCrumbs()
    {
        $this->crumbs->record("Something happened");
        $this->crumbs->clear();

        $this->assertEmpty($this->crumbs->getCrumbs());
    }

}