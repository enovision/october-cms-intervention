<?php

namespace Enovision\Intervention\classes\markdown;

use Config;
use Exception;
use Illuminate\Support\Facades\Storage;
use Enovision\Intervention\classes\filters\Base as BaseFilter;

class Helper
{
    protected $parser;

    private $url;
    private $urlOriginal;
    private $urlNew;

    private $basename;
    private $image;

    private $actions = [];
    private $actionsNotUsed = [];

    private $pathInfo = [
        'path'     => null,
        'filename' => null,
        'url'      => null,
        'fullPath' => null
    ];

    var $newPath = '';

    public function beforeParseProcessImages($text)
    {
        $regex = '/(?:!\[(.*?)\]\((.*?)\))/';

        preg_match_all($regex, $text, $matches, PREG_SET_ORDER, 0);

        foreach ($matches as $match) {

            $times = 1;
            $complete = $match[0];
            $imageUrl = $match[2];

            $replace = $this->beforeParseProcessImage($imageUrl, $text, $complete);

            $text = str_replace($imageUrl, $replace, $text, $times);

        }

        return $text;

    }

    public function beforeParseProcessImage($url, $text, $markdownTag = null)
    {
        $this->url = $url;

        $urlParts = parse_url($url, PHP_URL_QUERY);
        parse_str($urlParts, $parsedUrl);

        $urlPath = parse_url($url, PHP_URL_PATH);
        $this->basename = basename($urlPath);

        if (is_array($parsedUrl) && count($parsedUrl) > 0) {

            $this->actions = [];

            $this->collectActions($parsedUrl);

            if (count($this->actions) > 0) {

                $path = $this->createNewPath();

                // debug($path);

                $pathInfo = BaseFilter::setPath($path, $this->basename);

                if (BaseFilter::hasCached($pathInfo['fullPath'])) {
                    // return $this->buildNewUrl($pathInfo['url']);
                }

                $image = $this->processActions($url, $text, $markdownTag);

                $image->save($pathInfo['fullPath']);

                return $this->buildNewUrl($pathInfo['url']);
            }
        }

        return $url;
    }

    private function buildNewUrl($url)
    {
        $concat = '?';
        $query = '';

        if (count($this->actionsNotUsed) > 0) {
            foreach ($this->actionsNotUsed as $action => $parameter) {
                $query .= $concat . $action;
                if (!empty($parameter)) {
                    $query .= '=' . $parameter;
                }
                $concat = '&';
            }
        }

        $this->urlNew = $url . $query;

        return $this->urlNew;
    }

    /**
     * Collects the Actions on an image into $this->actions array
     *
     * @param $parsedUrl
     */
    private function collectActions($parsedUrl)
    {
        foreach ($parsedUrl as $action => $parameter) {

            if (array_key_exists($action, BaseFilter::$beforeParseActions)) {
                $this->actions[strtolower($action)] = $parameter;
            } else {
                $this->actionsNotUsed[strtolower($action)] = $parameter;
            }
        }
    }

    function processActions($url, $text = null, $markdownTag = null)
    {
        $this->urlOriginal = $url;
        $this->url = strtok($url, '?');
        $this->image = BaseFilter::makeImage($this->url);

        /**
         * processed in order of BaseFilter::$beforeParseActions !!!
         */
        foreach (BaseFilter::$beforeParseActions as $action => $class) {

            if (array_key_exists($action, $this->actions)) {

                if (method_exists($class, 'execute')) {
                    $this->image = $class::execute(
                        $this->image,
                        $this->actions[$action],
                        $this->url,
                        $text,
                        $markdownTag
                    );
                }
            }
        }

        return $this->image;
    }

    private function createNewPath()
    {
        $path = '';

        foreach ($this->actions as $action => $parameters) {
            $folder = strtolower($action);
            if (array_key_exists(strtolower($action), BaseFilter::$actionFolder)) {
                $folder = BaseFilter::$actionFolder[$action];
            }

            $parametersExploded = explode(',', $parameters);

            foreach ($parametersExploded as $parameter) {
                if (!empty($parameter)) {
                    $folder .= '-' . $parameter;
                }
            }

            $path .= (empty($folder) ? '' : '/') . $folder;
        }

        // debug($path);

        return $this->newPath = $path;

    }

    private function setPath($path, $folders)
    {
        $config = Config::get('enovision.intervention::thumbnailer', false);
        $fileName = basename($path);

        $uploadFolder = Config::get('cms.storage.uploads.folder');
        $pathThumbnailer = $uploadFolder . '/' . $config['thumbnailPath'];
        $subFolder = self::getSubFolders($folders);

        /* check if folder exists, otherwise create it */
        $directories = Storage::allDirectories($pathThumbnailer . $subFolder);

        if (count($directories) === 0) {
            Storage::makeDirectory($pathThumbnailer . $subFolder);
        }

        $fullPath = storage_path('app') . '/' . $pathThumbnailer . $subFolder . '/' . $fileName;

        self::$pathInfo = [
            'path'     => $path,
            'filename' => $fileName,
            'url'      => url('storage/app/' . $pathThumbnailer . $subFolder . '/' . $fileName),
            'fullPath' => $fullPath
        ];
    }

    /**
     * @return bool
     */
    private function hasCached()
    {
        $exist = file_exists(self::$pathInfo['fullPath']);
        return $exist;
    }

    /**
     * @param $path
     *
     * @return array
     */
    private function getPathContent($path)
    {
        try {
            $content = file_get_contents($path);
            return [$content, true];
        } catch (Exception $e) {
            $_404Image = Config::get('enovision.intervention::thumbnailer.404imageLocation', '404.jpg');
            $content = file_get_contents($_404Image);

            return [$content, $_404Image];
        }
    }

    private function getSizeFolder($width, $height)
    {
        return sprintf('%s%s%s',
            $width ? 'w' . $width : '',
            $height && $width ? 'x' : '',
            $height ? 'h' . $height : ''
        );
    }

    private function getImageSize($path)
    {
        $tempImg = \Intervention\Image\ImageManagerStatic::make($path);
        $width = $tempImg->width();
        $height = $tempImg->height();
        $tempImg->destroy();
        return [$width, $height];
    }

    private function getSubFolders($folders)
    {
        $subFolders = '';
        foreach ($folders as $folder) {
            $subFolders .= '/' . $folder;
        }

        return $subFolders;
    }

}