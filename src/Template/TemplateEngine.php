<?php

namespace SlashTrace\Template;

interface TemplateEngine
{
    /**
     * @param string $template
     * @param array $data
     * @return string
     */
    public function render($template, array $data);
}