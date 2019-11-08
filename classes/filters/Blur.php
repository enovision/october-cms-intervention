<?php
namespace Enovision\Intervention\classes\filters;

use Enovision\Intervention\classes\filters\Base as BaseFilter;

abstract class Blur extends BaseFilter {

    static function execute($image, $args = '')
    {
        self::$args = explode(',', $args);

        $amount = self::setArg('a', 1);

        $image->blur($amount);

        return $image;
    }
}