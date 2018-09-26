<?php

namespace SlashTrace\Template;

class ResourceLoader
{
    public function stylesheet($file)
    {
        $path = $this->getAssetsDirectory() . "/stylesheets/$file";

        $return = '<style type="text/css">';
        $return .= $this->loadResource($path);
        $return .= '</style>';

        return $return;
    }

    public function script($file)
    {
        $path = $this->getAssetsDirectory() . "/scripts/$file";

        $return = '<script type="text/javascript">';
        $return .= $this->loadResource($path);
        $return .= '</script>';

        return $return;
    }

    public function getViewsDirectory()
    {
        return $this->getRootDirectory() . "/views";
    }

    private function getAssetsDirectory()
    {
        return $this->getRootDirectory() . "/assets";
    }

    private function getRootDirectory()
    {
        return dirname(__DIR__) . "/Resources";
    }

    private function loadResource($file)
    {
        return file_get_contents($file);
    }
}