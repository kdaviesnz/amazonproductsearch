<?php

namespace kdaviesnz\amazon;

interface IAmazonGeo
{

    public function getCountry();
    public function getCity();
    public function getRegion();
    public function getRegionCode();
    public function getAreaCode();
    public function getCountryCode();
    public function getContinentCode();
    public function getLatitude();
    public function getLongitude();
    public function getCurrencyCode();
    public function getCurrencySymbol();
    public function getCurrencySymbolUtf8();
    public function getCurrrencyConverter();
    public function getIp();
    public function getAmazonCountryExt();
    public function getAmazonLang();
    public static function get_coordinates( $address );

}