<?php
declare(strict_types=1);


namespace kdaviesnz\amazon;


interface IAmazonParser
{
    public function parse_cart( $cart_xml );
    public function parse_search_results( $search_results_xml );
    public function parse_item_search_results( $search_results_xml );
    public function parse_category_search_results( $category_search_results_xml );
    public function parse_related_items_search_results( $search_results_xml );
    public function parse_frequently_bought_together_search_results( $search_results_xml );
    public function generate_products( $items );

}