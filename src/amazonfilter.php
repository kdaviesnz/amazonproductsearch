<?php
 // must be first line


namespace kdaviesnz\amazon;



class AmazonFilter implements IAmazonFilter
{

    public static function noFilter()  {
        return function( $items ) {
            return $items;
        };
    }

}