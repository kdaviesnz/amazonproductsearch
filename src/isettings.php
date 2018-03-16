<?php

namespace kdaviesnz\amazon;


interface ISettings
{
    public static function get( $name );
    public static function set( ISetting $setting );
    public static function all();
    public static function save( $values );
    public static function reset();
    public static function verify();
}
