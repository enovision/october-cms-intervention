<?php
/**
 * Created by PhpStorm.
 * User: jvandemerwe
 * Date: 9/23/18
 * Time: 1:59 PM
 */

namespace Enovision\Intervention\classes\twig;

use Enovision\Intervention\classes\Image as ImageManager;

abstract class Filters
{

    static function thumbnailer($path, $args)
    {
        $img = ImageManager::thumbnailer($path, $args);

        return $img;
    }

    static function filterResize($path, $height = 100, $width = false, $keepAspectRation = true, $upsize = true, $args = [])
    {
        $img = ImageManager::resize($path, $height, $width, $keepAspectRation, $upsize);

        if (is_array($args) && count($args) > 0) {
            $img = self::processArgs($img, $args);
        }

        return $img;
    }

    static function filterCrop($path, $width = 100, $height = null, $x = null, $y = null, $args = [])
    {
        $img = ImageManager::crop($path, $width, $height, $x, $y);

        if (is_array($args) && count($args) > 0) {
            $img = self::processArgs($img, $args);
        }

        return $img;
    }

    static function filterFit($path, $width = 100, $height = null, $upsize = false, $position = null, $args = [])
    {
        $position = $position === null ? 'center' : $position;

        $img = ImageManager::fit($path, $width, $height, $upsize, $position);

        if (is_array($args) && count($args) > 0) {
            $img = self::processArgs($img, $args);
        }

        return $img;
    }


    static function filterRotate($path, $angle = 0, $bgColor = '#ffffff')
    {
        $img = ImageManager::rotate($path, $angle, $bgColor);
        return $img;
    }

    static function filterFlip($path, $mode = null)
    {
        if (in_array($mode, ['h', 'v', 'vh'])) {
            $img = ImageManager::flip($path, $mode);
            return $img;
        } else {
            return $path;
        }
    }

    static function filterGreyscale($path, $brightness = 0)
    {
        $img = ImageManager::greyscale($path, $brightness);
        return $img;
    }

    static function filterBrightness($path, $brightness = 0)
    {
        $img = ImageManager::brightness($path, $brightness);
        return $img;
    }

    static function filterColorize($path, $red = 0, $green = 0, $blue = 0) {
        $img = ImageManager::colorize($path, [$red, $green, $blue]);
        return $img;
    }

    static function processArgs($img, $args)
    {
        if (!is_array($args) || count($args) === 0) {
            return $img;
        }

        foreach ($args as $key => $arg) {
            $img = ImageManager::thumbnailer($img, $key, $arg);
        }

        return $img;
    }
}