<?php

namespace kdaviesnz\amazon;


interface IAmazonKeywordsTool
{

    public static function fetch( $keyword, $ip, $depth, $max_depth );
    public static function get_best_keywords();

}