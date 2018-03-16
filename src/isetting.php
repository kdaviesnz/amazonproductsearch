<?php

namespace kdaviesnz\amazon;


interface ISetting
{

    public function save( $replace );
    public function value();
    public function __toString();

}