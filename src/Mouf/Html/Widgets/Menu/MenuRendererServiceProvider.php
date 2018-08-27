<?php


namespace Mouf\Html\Widgets\Menu;

use Mouf\Html\Renderer\AbstractPackageRendererServiceProvider;

class MenuRendererServiceProvider extends AbstractPackageRendererServiceProvider
{
    /**
     * Returns the path to the templates directory.
     *
     * @return string
     */
    public static function getTemplateDirectory(): string
    {
        return __DIR__.'/../../../templates';
    }
}
