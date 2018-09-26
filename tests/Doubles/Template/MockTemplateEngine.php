<?php

namespace SlashTrace\Tests\Doubles\Template;

use SlashTrace\Template\TemplateEngine;

class MockTemplateEngine implements TemplateEngine
{
    /** @var string */
    private $renderTemplate;

    /** @var array */
    private $renderData;

    public function render($template, array $data)
    {
        $this->renderTemplate = $template;
        $this->renderData = $data;
        return "";
    }

    public function getRenderTemplate()
    {
        return $this->renderTemplate;
    }

    public function getRenderData()
    {
        return $this->renderData;
    }
}