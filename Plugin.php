<?php namespace Enovision\Intervention;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use System\Classes\PluginBase;

use Enovision\Intervention\classes\twig\Filters;
use Enovision\Intervention\classes\markdown\Helper as MarkdownHelper;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'enovision.intervention::lang.plugin.name',
            'description' => 'enovision.intervention::lang.plugin.description',
            'author'      => 'Enovision IT & Web Services',
            'icon'        => 'icon-picture-o',
            'homepage'    => 'https://github.com/enovision/oc-intervention-plugin'
        ];
    }

    /**
     * @return array|void
     * Replace the notices in the markdown editor of the blog component
     */
    public function boot()
    {
        $this->app->singleton('Enovision\ImageProcessor', function ($app) {
            $helper = new MarkdownHelper();
            return $helper;
        });

        Event::listen('markdown.beforeParse', function ($data) {
            $helper = App::make('Enovision\ImageProcessor');
            $data->text = $helper->beforeParseProcessImages($data->text);
        });

    }

    /**
     * Register TWIG extensions
     * @return array
     */
    public function registerMarkupTags()
    {
        return [
            'functions' => [
                //	'resize'             => [Filters::class, 'filterResize'],
                //	'crop'               => [Filters::class, 'filterCrop'],
                //	'thumbnailer'        => [Filters::class, 'thumbnailer']
            ],
            'filters'   => [
                'resize'      => [Filters::class, 'filterResize'],
                'crop'        => [Filters::class, 'filterCrop'],
                'fit'         => [Filters::class, 'filterFit'],
                'thumbnailer' => [Filters::class, 'thumbnailer']
            ]
        ];
    }
}
