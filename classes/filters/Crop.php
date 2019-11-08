<?php
namespace Enovision\Intervention\classes\filters;

use Enovision\Intervention\classes\filters\Base as BaseFilter;

abstract class Crop extends BaseFilter
{
    static function execute(\Intervention\Image\Image $image, $args = '')
    {
        self::$args = explode(',', $args);

        $width = self::setArg('w', 100);
        $height = self::setArg('h', null);
        $x = self::setArg('x', null);
        $y = self::setArg('y', null);

        $image->crop($width, $height, $x, $y);

        return $image;
    }
}