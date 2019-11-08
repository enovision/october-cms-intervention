<?php
namespace Enovision\Intervention\classes\filters;

use Enovision\Intervention\classes\filters\Base as BaseFilter;

abstract class Circle extends BaseFilter {

    static function execute($image, $args = '')
    {
        self::$args = explode(',', $args);

        $diameter = self::setArg('diameter', 50);
        $pos_x = self::setArg('posx', 0);
        $pos_y = self::setArg('posy', 0);
        $bgColor = self::setArg('bg', false);
        $border = self::setArg('border_size', 0);
        $borderColor = self::setArg('border_color', '#000');

        $callback = function($draw) use($bgColor, $border, $borderColor) {
            if ($bgColor !== false) {
                $draw->background($bgColor);
            }
            if ((int) $border > 0 ) {
                $draw->border($border, $borderColor);
            }
        };

        $image->circle($diameter, $pos_x, $pos_y, $callback);

        return $image;
    }
}