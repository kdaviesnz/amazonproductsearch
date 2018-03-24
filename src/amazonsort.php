<?php

namespace kdaviesnz\amazon;



class AmazonSort implements IAmazonSort
{

    public static function noSort()  {
        return function ( $items ) {
            return $items;
        };
    }

    /**
     * Sort items by average RFF.
     *
     * @param array $items Array of IAmazonProduct products.
     * @return array
     */
    public static function sortByBest() {
        return function( $items ) {
            global $wpdb;

            $best_items = array();

            $asins = array();
            foreach ($items as $product) {
                $asins[] = $product->getAsin();
            }

            $asins_string = "'" . implode("','", $asins) . "'";

            $sql = $wpdb->prepare(
                "SELECT `AIN`, `keyType`, `keyValue` FROM `wp_amazon_amazon_products` 
              WHERE `avgRRF` > 0 
              AND `keyValue` IN ( $asins_string )
              ORDER BY `avgRRF` ASC",
                ""
            );
            
            $records = $wpdb->get_results($sql);

            if (!empty($wpdb->last_error)) {
                echo $wpdb->last_error;
                throw new \Exception($wpdb->last_error);
            }
            if (!empty($records)) {
                foreach ($records as $record) {
                    $asin = $record->AIN;
                    $best_items[] = AmazonCache::getProduct($asin, '');
                }
            }

            return $best_items;
        };

    }


}