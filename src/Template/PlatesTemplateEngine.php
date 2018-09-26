<?php

namespace SlashTrace\Template;

use League\Plates\Engine;

class PlatesTemplateEngine implements TemplateEngine
{
    /** @var ResourceLoader */
    private $resourceLoader;

    /** @var Engine */
    private $engine;

    public function __construct(ResourceLoader $resourceLoader)
    {
        $this->resourceLoader = $resourceLoader;
    }

    /**
     * Renders the main template, making the data available globally to all sub-templates
     *
     * @see http://platesphp.com/templates/data/
     * @param string $template
     * @param array $data
     * @return string
     */
    public function render($template, array $data)
    {
        $engine = $this->getEngine();
        $engine->addData($data);
        return $engine->render($template);
    }

    /**
     * @return Engine
     */
    private function getEngine()
    {
        if (is_null($this->engine)) {
            $this->engine = new Engine($this->resourceLoader->getViewsDirectory());
        }
        return $this->engine;
    }
}