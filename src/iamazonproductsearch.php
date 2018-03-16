<?php


namespace kdaviesnz\amazon;

interface IAmazonProductSearch
{

    public static function getPostRelatedProducts( $wp_post, $to, $sortFunction, $filterFunction, $relationshipType, $searchIndex, $search, $from, $endPoint, $uri ); // array of IAmazonProduct
    public static function search( $searchTerm, $to, $sortFunction, $filterFunction, $relationshipType, $searchIndex, $search, $from, $endPoint, $uri );  // array of IAmazonProduct
    public static function itemSearch( $asin, $endPoint, $uri );
    public static function categorySearch( $categoryID, $groups, $relationshipType, $endPoint, $uri );
    public static function bestProductsByCategorySearch( $categoryID, $relationshipType, $endPoint, $uri );
    public static function mostRecentProductsByCategorySearch( $categoryID, $relationshipType, $endPoint, $uri );
    public static function bestProductsSearch( $searchTerm, $to, $searchIndex, $filterFn, $relationshipType, $from , $endPoint, $uri );
    
}