<?php
 // must be first line

namespace kdaviesnz\amazon;


class AmazonKeyword implements IAmazonKeyword
{
    
    public $name = '';
    public $categories = '';
    public $variations = '';
    public $rrf =  null;

    /**
     * AmazonKeyword constructor.
     *
     * @param string $name
     * @param string $categories
     * @param string $variations
     */
    public function __construct($name, $categories, $variations, $rrf )
    {
        $this->name = $name;
        $this->categories = $categories;
        $this->variations = $variations;
        if ( $rrf > 0 ) {
            $this->rrf = $rrf;
        }
    }

    public function rrf() {

        if ( $this->rrf > 0 ) {
            return $this->rrf;

        }
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT AVG( `wp_amazon_amazon_products`.`avgRRF` ) as `rrf` FROM `wp_amazon_search`, `wp_amazon_amazon_products` WHERE `wp_amazon_search`.`keyValue` = `wp_amazon_amazon_products`.`keyValue` AND `wp_amazon_search`.`searchTerm` = '%s'",
            $this->name
        );

        $record = $wpdb->get_row( $sql );

        $this->rrf = $record->rrf;

        return (float) $record->rrf;

    }

}
