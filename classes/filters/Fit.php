<?php
namespace Enovision\Intervention\classes\filters;

use Enovision\Intervention\classes\filters\Base as BaseFilter;

/**
 * Class Fit
 *
 * http://image.intervention.io/api/fit
 *
 * @package Enovision\Intervention\classes\markdown\filters
 *
 */
abstract class Fit extends BaseFilter
{

    static $validPositions = [
        'top-left', 'top', 'top-right;', 'left', 'center', 'right', 'bottom-left', 'bottom', 'bottom-right'
    ];

    static function execute($image, $args = '')
    {
        self::$args = explode(',', $args);

        $width = self::setArg('w', 100);
        $height = self::setArg('h', null);
        $upsize = self::setArg('upsize', false);
        $position = self::setArg('position', 'center', self::$validPositions);

        $callback = function ($constraint) use ($upsize) {
            if ($upsize) {
                $constraint->upsize();
            }
        };

        $image->fit($width, $height, $callback, $position);

        return $image;
    }
}