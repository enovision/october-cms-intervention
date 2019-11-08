<?php

namespace Enovision\Intervention\classes\filters;

use Enovision\Intervention\classes\filters\Base as BaseFilter;

abstract class Lightbox extends BaseFilter
{
    static function execute($image, $args = '', $url = null, &$text = null, $markdownTag = null)
    {
        self::$args = explode(',', $args);

        $class = self::setArg('class', 'image-with-lightbox');

        if ($text !== null && $markdownTag !== null) {
            $slimbox_caption = 'soepjurk';

            $pre = '<a class="'. $class  .'" href="' . $url . '" rel="lightbox[this_page]" title="' . $slimbox_caption . '">';
            $post = '</a>';

            debug($pre, $post);

            $replaceTag = $pre . $markdownTag . $post;

            $text = str_replace($markdownTag, $replaceTag, $text);

            debug($text);
        }

        return $image;
    }

}