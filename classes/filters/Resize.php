<?php
namespace Enovision\Intervention\classes\filters;

use Enovision\Intervention\classes\filters\Base as BaseFilter;

abstract class Resize extends BaseFilter {

    static function execute(\Intervention\Image\ImageManager $image, $args = '')
    {
        self::$args = explode(',', $args);

        $width = self::setArg('w', false);
        $height = self::setArg('h', 100);
        $keepAspectRatio = self::setArg('keepratio', true);
        $upsize = self::setArg('upsize', true);

        $callback = function ($constraint) use ($keepAspectRatio, $upsize) {
            if ($keepAspectRatio) {
                $constraint->aspectRatio();
            }
            if ($upsize === true) {
                $constraint->upsize();
            }
        };

        $image->resize($width, $height, $callback);

        return $image;
    }

}