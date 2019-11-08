<?php
/**
 * Created by PhpStorm.
 * User: jvandemerwe
 * Date: 9/23/18
 * Time: 1:59 PM
 */

namespace Enovision\Intervention\classes\filters;

use Config;
use Exception;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as ImageManager;

abstract class Base
{

    static $args;

    static $pathInfo = [
        'path' => null,
        'filename' => null,
        'url' => null,
        'fullPath' => null
    ];

    static $beforeParseActions = [
        'resize' => \Enovision\Intervention\classes\filters\Resize::class,
        'crop' => \Enovision\Intervention\classes\filters\Crop::class,
        'fit' => \Enovision\Intervention\classes\filters\Fit::class,
        'blur' => \Enovision\Intervention\classes\filters\Blur::class,
        'circle' => \Enovision\Intervention\classes\filters\Circle::class,
        'flip' => \Enovision\Intervention\classes\filters\Flip::class,
        'brightness' => \Enovision\Intervention\classes\filters\Brightness::class,
        'greyscale' => \Enovision\Intervention\classes\filters\Greyscale::class,
        'colorize' => \Enovision\Intervention\classes\filters\Colorize::class,
        'rotate' => \Enovision\Intervention\classes\filters\Rotate::class,
        'lightbox' => \Enovision\Intervention\classes\filters\Lightbox::class
    ];

    static $afterParseActions = [];

    static $actionFolder = [
        'resize' => 'resized',
        'crop' => 'cropped',
        'fit' => 'fitted',
        'blur' => 'blurred',
        'circle' => 'circle',
        'flip' => 'flipped',
        'brightness' => 'bright',
        'greyscale' => 'gs',
        'colorize' => 'color',
        'rotate' => 'rotated',
        'filter' => 'filtered',
        'lightbox' => null
    ];

    static function makeImage($path)
    {
        $image = ImageManager::make($path); // without https://somedomain.com/
        return $image;
    }

    static function setArg($arg, $fallback = null, $validValues = [])
    {
        $returnValue = $fallback;

        foreach (self::$args as $a) {
            if (substr($a, 0, strlen($arg)) === $arg) {
                $returnValue = substr($a, strlen($arg));
                if (count($validValues) === 0) {
                    $validValues[] = $returnValue;
                }
            }
        }

        return in_array($returnValue, $validValues) ? $returnValue : $fallback;
    }

    static function thumbnailer($path, $args = [])
    {
        try {
            $img = ImageManager::make($path);

            foreach ($args as $action => $arg) {
                if (in_array($action, self::$validActions)) {
                    $img = self::$action($img, $arg);
                }
            }

            return $img;
        } catch (Exception $e) {
            throw $e;
        }

    }

    static function setPath($path, $fileName)
    {
        $config = Config::get('enovision.intervention::thumbnailer', false);

        $uploadFolder = Config::get('cms.storage.uploads.folder');
        $pathThumbnailer = $uploadFolder . '/' . $config['thumbnailPath'];

        /* check if folder exists, otherwise create it */
        $directories = Storage::allDirectories($pathThumbnailer . $path);

        if (count($directories) === 0) {
            Storage::makeDirectory($pathThumbnailer . $path);
        }

        $fullPath = storage_path('app') . '/' . $pathThumbnailer . $path . '/' . $fileName;

        self::$pathInfo = [
            'path' => $path,
            'filename' => $fileName,
            'url' => url('storage/app/' . $pathThumbnailer . $path . '/' . $fileName),
            'fullPath' => $fullPath
        ];

        return self::$pathInfo;
    }

    /**
     * @return bool
     */
    static function hasCached($path)
    {
        return file_exists($path);
    }

    /**
     * @param $path
     *
     * @return array
     */
    static function getPathContent($path)
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

    static function getSizeFolder($width, $height)
    {
        return sprintf('%s%s%s',
            $width ? 'w' . $width : '',
            $height && $width ? 'x' : '',
            $height ? 'h' . $height : ''
        );
    }

    static function getImageSize($path)
    {
        $tempImg = ImageManager::make($path);
        $width = $tempImg->width();
        $height = $tempImg->height();
        $tempImg->destroy();
        return [$width, $height];
    }

    static function getSubFolders($folders)
    {
        $subFolders = '';
        foreach ($folders as $folder) {
            $subFolders .= '/' . $folder;
        }

        return $subFolders;
    }
}