<?php
namespace Enovision\Intervention\classes\filters;

use Enovision\Intervention\classes\filters\Base as BaseFilter;

abstract class Flip extends BaseFilter {

    static function execute($image, $args = '')
    {
        self::$args = explode(',', $args);

        $mode = in_array('v', self::$args) ? 'v' : '';
        $mode .= in_array('h', self::$args) ? 'h' : '';

        if ($mode === 'v' || $mode === 'h') {
            $image->flip($mode);
        } elseif ($mode === 'vh') {
            $image->flip('v')->flip('h');
        }

        return $image;
    }
}