<?php
namespace Enovision\Intervention\classes\filters;

use Enovision\Intervention\classes\filters\Base as BaseFilter;

abstract class Brightness extends BaseFilter {

    static function execute($image, $args = '')
    {
        self::$args = explode(',', $args);

        $brightness = self::setArg('b', 0);

        $image->brightness($brightness);

        return $image;

    }

}