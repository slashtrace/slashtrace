<?php

namespace SlashTrace\DebugRenderer;

use SlashTrace\Event;
use SlashTrace\System\HasSystemProvider;
use SlashTrace\Template\ResourceLoader;
use SlashTrace\Template\PlatesTemplateEngine;
use SlashTrace\Template\TemplateEngine;
use SlashTrace\Template\TemplateHelper;

class DebugWebRenderer implements DebugRenderer
{
    use HasSystemProvider;

    /** @var TemplateEngine */
    private $templateEngine;

    /** @var ResourceLoader */
    private $resourceLoader;

    public function render(Event $event)
    {
        $system = $this->getSystem();
        $templateEngine = $this->getTemplateEngine();

        $system->cleanOutputBuffer();
        $system->output($templateEngine->render("index", [
            "pageTitle"      => $this->getPageTitle($event),
            "event"          => $event,
            "resourceLoader" => $this->getResourceLoader(),
            "templateHelper" => new TemplateHelper()
        ]));
    }

    private function getPageTitle(Event $event)
    {
        $exceptions = $event->getExceptions();
        if (!count($exceptions)) {
            return "An error occured";
        }
        return $exceptions[0]->getMessage();
    }

    /**
     * @return TemplateEngine
     */
    public function getTemplateEngine()
    {
        if (is_null($this->templateEngine)) {
            $this->templateEngine = new PlatesTemplateEngine($this->getResourceLoader());
        }
        return $this->templateEngine;
    }

    public function setTemplateEngine(TemplateEngine $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    /**
     * @return ResourceLoader
     */
    private function getResourceLoader()
    {
        if (is_null($this->resourceLoader)) {
            $this->resourceLoader = new ResourceLoader();
        }
        return $this->resourceLoader;
    }
}