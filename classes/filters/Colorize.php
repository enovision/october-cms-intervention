<?php
namespace Enovision\Intervention\classes\filters;

use Enovision\Intervention\classes\filters\Base as BaseFilter;

abstract class Colorize extends BaseFilter {

    static function execute($image, $args = '')
    {
        self::$args = explode(',', $args);

        $red = self::setArg('r', 0);
        $green = self::setArg('g', 0);
        $blue = self::setArg('b', 0);

        $image->colorize($red, $green, $blue);

        return $image;
    }
}