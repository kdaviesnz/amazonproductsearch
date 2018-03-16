<?php

namespace kdaviesnz\amazon;

interface IAmazonCache
{
    public static function cacheKeywords( $keyword );
    public static function getKeywords( $keyword, $rrf, $depth );

    public static function cacheProduct( IAmazonProduct $product, $relationshipType, $related_products );
    public static function getProduct( $ain );
    
    public static function cacheCategory( AmazonCategory $category );
    public static function getCategory( $categoryID );
    public static function getAncestorCategories( $ancestors, $categoryID, $depth );

    public static function get_ancestor_rrfs( $rrfs, $ancestor_categories, $salesRank, $depth );

    public static function cacheSearch( $searchTerm, $products, $searchType );
    public static function performCachedSearch( $searchTerm, $searchType ); // array of IAmazonProducts

    public static function generate_products( $records );

    public static function getLSI( $primary_word );
    public static function cacheLSI( $primary_word, $lsi );

    //  $categories[] = new AmazonCategory( (string) $node->BrowseNodeId, (string) $node->Name, $ancestor_categories, $items_in_category['total_results'] );
    public static function get_related_products($product, $relationshipType);

}