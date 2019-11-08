<?php
/**
 * Created by PhpStorm.
 * User: jvandemerwe
 * Date: 9/23/18
 * Time: 1:59 PM
 */

namespace Enovision\Intervention\classes;

use Config;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as ImageManager;
use Exception;

abstract class Image
{

    static $validActions = ['resize', 'crop', 'fit', 'blur', 'circle', 'colorize', 'flip', 'rotate', 'filter'];

    static $folderPath = '';

    static $pathInfo = [
        'path'     => null,
        'filename' => null,
        'url'      => null,
        'fullPath' => null
    ];

    static function initImage($path, $width, $height, $prefix)
    {

        self::setPath($path, [self::getSizeFolder($width, $height), $prefix]);

        if (self::hasCached()) {
            return false;
        }

        $content = self::getPathContent(self::$pathInfo['path']);

        if ($content[1] !== true) {
            self::setPath($content[1], [self::getSizeFolder($width, $height), $prefix]);
        }

        return $content;
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

    /**
     * @param $path
     * @param int $width
     * @param int $height
     * @param int $x
     * @param int $y
     *
     * @return mixed
     */
    static function crop($path, $width = 100, $height = 100, $x = 25, $y = 25)
    {

        try {

            $config = Config::get('enovision.intervention::thumbnailer', false);

            if ($config === false) {
                return 'no config';
            }

            $prefix = 'cropped';

            self::setPath($path, [self::getSizeFolder($width, $height), $prefix]);

            $img = ImageManager::cache(function ($image) use ($width, $height, $x, $y) {

                $content = self::getPathContent(self::$pathInfo['path']);

                if ($content[1] !== true) {

                    self::setPath($content[1], [self::getSizeFolder($width, $height), $prefix]);
                }

                $image->make($content[0])
                    ->crop($width, $height, $x, $y);

            }, $config['cacheTimeout']);

            $img->save(self::$pathInfo['fullPath']);

            return self::$pathInfo['url'];

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $path
     * @param int $width
     * @param int $height
     * @param int $x
     * @param int $y
     *
     * @return mixed
     */
    static function fit($path, $width = 100, $height = null, $upsize = false, $position = 'center')
    {

        $isGreyScale = $upsize === 'greyscale' || $upsize === 'grayscale';
        $upsize = $isGreyScale ? false : $upsize;

        try {

            $prefix = $isGreyScale ? 'fitted-gs' : 'fitted';

            $result = self::initImage($path, $width, $height, $prefix);

            if ($result === false) {
                return self::$pathInfo['url'];
            }

            $image = ImageManager::make($result[0]);

            if ($upsize === true) {
                $image->fit($width, $height, function ($constraint) {
                    $constraint->upsize();
                });
            } else {
                $image->fit($width, $height);
            }

            if ($isGreyScale) {
                $image->greyscale()->brightness(10);
            }

            $image->save(self::$pathInfo['fullPath']);

            return self::$pathInfo['url'];

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $path
     * @param int $width
     * @param int $height
     * @param bool $keepAspectRatio
     * @param bool $upsize
     *
     * @return mixed
     */
    static function resize($path, $width = 100, $height = 100, $keepAspectRatio = true, $upsize = false, $hasCallback = true)
    {

        $isGreyScale = $upsize === 'greyscale' || $upsize === 'grayscale';
        $upsize = $isGreyScale ? true : $upsize;

        try {

            $config = Config::get('enovision.intervention::thumbnailer', false);

            if ($config === false) {
                return 'no config';
            }

            $prefix = $isGreyScale ? 'resized-gs' : 'resized';

            self::setPath($path, [$prefix, self::getSizeFolder($width, $height)]);

            if (self::hasCached()) {
                return self::$pathInfo['url'];
            }

            $content = self::getPathContent(self::$pathInfo['path']);

            if ($content[1] !== true) {
                self::setPath($content[1], [$prefix, self::getSizeFolder($width, $height)]);
            }

            $callback = function ($constraint) use ($keepAspectRatio, $upsize) {
                if ($keepAspectRatio) {
                    $constraint->aspectRatio();
                }
                if ($upsize === true) {
                    $constraint->upsize();
                }
            };

            $image = ImageManager::make($content[0]);

            if ($hasCallback) {
                $image->resize($width, $height, $callback);
            } else {
                $image->resize($width, $height);
            }

            if ($isGreyScale) {
                $image->greyscale();
            }

            $image->save(self::$pathInfo['fullPath']);

            return self::$pathInfo['url'];

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $path
     * @param $args
     *
     * @return mixed
     */
    static function colorize($path, $colors = [null, null, null])
    {
        try {
            $red = $colors[0];
            $green = $colors[1];
            $blue = $colors[2];

            $prefix = 'color/' . "r{$red}" . "g{$green}" . "b{$blue}";
            $imageSizes = self::getImageSize($path);
            $width = $imageSizes[0];
            $height = $imageSizes[1];

            $result = self::initImage($path, $width, $height, $prefix);

            if ($result === false) {
                return self::$pathInfo['url'];
            }

            $image = ImageManager::make($result[0])
                ->colorize((int)$red, (int)$green, (int)$blue);

            $image->save(self::$pathInfo['fullPath']);

            return self::$pathInfo['url'];

        } catch (Exception $e) {
            throw $e;
        }

        try {
            $img->colorize($red, $green, $blue);

            return $img;
        } catch (Exception $e) {
            throw $e;
        }

    }

    /**
     * @param $path
     * @param $args
     *
     * @return mixed
     */
    static function greyscale($path, $brightness = 0)
    {
        try {
            $prefix = 'gs/' . "b{$brightness}";
            $imageSizes = self::getImageSize($path);
            $width = $imageSizes[0];
            $height = $imageSizes[1];

            $result = self::initImage($path, $width, $height, $prefix);
            if ($result === false) {
                return self::$pathInfo['url'];
            }

            $image = ImageManager::make($result[0])
                ->greyscale()
                ->brightness((int)$brightness);

            $image->save(self::$pathInfo['fullPath']);

            return self::$pathInfo['url'];

        } catch (Exception $e) {
            throw $e;
        }

        try {
            $img->colorize($red, $green, $blue);

            return $img;
        } catch (Exception $e) {
            throw $e;
        }

    }

    /**
     * @param $path
     * @param $args
     *
     * @return mixed
     */
    static function brightness($path, $brightness = 0)
    {
        try {

            $prefix = "bright{$brightness}";
            $imageSizes = self::getImageSize($path);
            $width = $imageSizes[0];
            $height = $imageSizes[1];

            $result = self::initImage($path, $width, $height, $prefix);
            if ($result === false) {
                return self::$pathInfo['url'];
            }

            $image = ImageManager::make($result[0])
                ->brightness((int)$brightness);

            $image->save(self::$pathInfo['fullPath']);

            return self::$pathInfo['url'];

        } catch (Exception $e) {
            throw $e;
        }

        try {
            $img->colorize($red, $green, $blue);

            return $img;
        } catch (Exception $e) {
            throw $e;
        }

    }

    /**
     * @param $path
     * @param $args
     *
     * @return mixed
     */
    static function flip($path, $mode = 'v')
    {
        try {
            $prefix = 'flipped/' . $mode;
            $imageSizes = self::getImageSize($path);
            $width = $imageSizes[0];
            $height = $imageSizes[1];

            $result = self::initImage($path, $width, $height, $prefix);
            if ($result === false) {
                return self::$pathInfo['url'];
            }

            if ($result === false) {
                return self::$pathInfo['url'];
            }

            $image = ImageManager::make($result[0]);
            if ($mode === 'v' || $mode === 'h') {
                $image->flip($mode);
            } else {
                $image->flip('v')->flip('h');
            }

            $image->save(self::$pathInfo['fullPath']);
            return self::$pathInfo['url'];

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $img
     * @param integer $angle
     * @param string $bgColor
     *
     * @return mixed
     */
    static function rotate($path, $angle = 0, $bgColor = '#ffffff')
    {
        try {
            $prefix = 'rotated';
            $imageSizes = self::getImageSize($path);
            $width = $imageSizes[0];
            $height = $imageSizes[1];

            $result = self::initImage($path, $width, $height, $prefix);
            if ($result === false) {
                return self::$pathInfo['url'];
            }

            $image = ImageManager::make($result[0]);
            $image->rotate($angle, $bgColor);
            $image->save(self::$pathInfo['fullPath']);
            return self::$pathInfo['url'];

        } catch (Exception $e) {
            throw $e;
        }
    }

    static function setPath($path, $folders)
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
    static function hasCached()
    {
        $exist = file_exists(self::$pathInfo['fullPath']);
        return $exist;
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