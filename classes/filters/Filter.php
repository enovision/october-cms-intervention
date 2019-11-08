<?php
namespace Enovision\Intervention\classes\filters;

use Enovision\Intervention\classes\filters\Base as BaseFilter;

abstract class Filter extends BaseFilter {

    static function execute($image, $args = '')
    {
        self::$args = explode(',', $args);

        $angle = self::setArg('a', 0);
        $bgColor = self::setArg('bg', '#ffffff');

        $image->rotate($angle, $bgColor);

        return $image;
    }
}