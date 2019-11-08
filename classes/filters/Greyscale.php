<?php
namespace Enovision\Intervention\classes\filters;

use Enovision\Intervention\classes\filters\Base as BaseFilter;

abstract class Greyscale extends BaseFilter {

    static function execute($image, $args = '')
    {
        self::$args = explode(',', $args);

        $brightness = self::setArg('b', 0);

          $image
            ->greyscale()
            ->brightness($brightness);

        return $image;
    }
}