<?php


namespace kdaviesnz\amazon;

class AmazonCache implements IAmazonCache
{

    public static function get_categories_generator( $records, $depth, $ancestors ) {

        foreach( $records as $record ) {
            if ( $depth < 3 ) {
                $ancestors = AmazonCache::getAncestorCategories( $ancestors, $record->parentCategoryID, $depth + 1 );
            }
            $number_of_items = ! empty( $record->number_items ) ? $record->number_items : -1;
            $category = new AmazonCategory(
                $record->parentCategoryID,
                $record->category_name,
                $ancestors,
                $number_of_items * 1
            );
            //  $ancestors[] = $category;
            yield $category;
        }
    }

    public static function generate_products( $records) {
        foreach ($records as $record) {
            // $products[] = AmazonCache::getProduct($record->AIN, '');
            yield AmazonCache::getProduct($record->AIN, '');
        }
    }

    public static function ancestor_categories_generator( $records, $depth, $ancestors ) {
        foreach ($records as $record) {
            if ($depth < 3) {
                $ancestors = AmazonCache::getAncestorCategories($ancestors, $record->parentCategoryID, $depth + 1);
            }
            $number_of_items = !empty($record->number_items) ? $record->number_items : -1;
            $category = new AmazonCategory(
                $record->parentCategoryID,
                $record->category_name,
                $ancestors,
                $number_of_items * 1
            );
            yield $category;
        }
    }

    public static function related_products_generator( $records ) {
        foreach ($records as $record) {
            yield $record->relatedAIN;
        }
    }

    public static function get_related_products($product, $relationshipType){

        global $wpdb;
        //$wpdb = new AmazonDB( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );

        $related_products = get_transient('related' . $product->getAsin() );

        if ( $related_products == false ) {

            $related_products = array();
            $relatedASINs = array();

            $sql = $wpdb->prepare(
                "SELECT `relatedAIN` FROM `wp_amazon_related_products` 
            WHERE `AIN` = '%s' and `relationshipType`='%s'
            AND DATEDIFF( NOW(), `lastUpdated` ) < 7",
                $product->getAsin(),
                $relationshipType
            );
            $records = $wpdb->get_results($sql);
            if (!empty($wpdb->last_error)) {
                echo $wpdb->last_error;
                throw new \Exception($wpdb->last_error);
            }
            if (!empty($records)) {
                foreach (AmazonCache::related_products_generator() as $relatedAIN) {
                    $relatedASINs[] = $relatedAIN;
                }
                $related_products = AmazonAmazonProductSearch::itemSearch(implode(',', $relatedASINs), $relationshipType);
            } else {
                throw new \Exception('No records found');
            }

            // Cache for 7 days.
            set_transient('related_' . $product->getAsin(), $related_products, 3600 * 24 * 7);

        }

        return is_array($related_products)?$related_products($related_products):array();
    }


    /**
     * Recursive function to get ancestor categories.
     *
     * @param array $ancestors
     * @param string $categoryID
     * @return array
     * @throws \Exception
     * @see https://www.smashingmagazine.com/2012/06/diy-caching-methods-wordpress/
     */
    public static function getAncestorCategories($ancestors, $categoryID, $depth ) {

    	global $conn;

        $amazondb = new AmazonDB($conn);

        // @todo
       // $ancestors = get_transient('ancestors_' . $categoryID );
	    $ancestors = false;

        if ( empty($ancestors)) {

            $ancestors = array();

            $sql = $amazondb->prepare(
                "SELECT `categoryID`, `parentCategoryID`, `number_items`, `category_name` FROM `wp_amazon_categories_categories` , `wp_amazon_amazon_categories` where `wp_amazon_categories_categories`.`parentCategoryID` = `wp_amazon_amazon_categories`.`category_id` AND `categoryID` = '%s'",
                $categoryID
            );
            $records = $amazondb->get_results($sql);
            if (!empty($amazondb->last_error)) {
                echo $amazondb->last_error;
                throw new \Exception($amazondb->last_error);
            }
            if (!empty($records)) {
                foreach( AmazonCache::ancestor_categories_generator( $records, $depth, $ancestors ) as $category ) {
                    $ancestors[] = $category;
                }
            }

            // @todo
           // set_transient('ancestors_' . $categoryID, $ancestors, 3600 ); // Cache for one hour
        }

        return $ancestors;
    }

    public static function cache_related_items(IAmazonProduct $product, $relationshipType, $related_items) {

        global $wpdb;

        foreach($related_items as $related_product) {
            $sql = $wpdb->prepare(
                "INSERT INTO `wp_amazon_related_products` (`AIN`, `relatedAIN`, `lastUpdated`, `relationshipType`) VALUES ('%s', '%s', CURRENT_TIMESTAMP, '%s')
            ON DUPLICATE KEY UPDATE `lastUpdated`='%s';",
                $product->getAsin(),
                $related_product->getAsin(),
                $relationshipType,
                date('Y-m-d H:i:s')
            );
            $wpdb->query($sql);
            if (!empty($wpdb->last_error)) {
                echo $wpdb->last_error;
                throw new \Exception($wpdb->last_error);
            }
        }
    }

    public static function cacheLSI( $primary_word, $lsi ) {

        global $wpdb;

        $sql = $wpdb->prepare(
            "INSERT INTO `wp_amazon_lsi` (`primary_word`, `lsi`, `last_updated`) 
            VALUES ('%s', '%s', CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE `lsi` = '%s', `last_updated`='%s';",
            $primary_word,
            $lsi,
            $lsi,
            date('Y-m-d H:i:s')
        );
        $wpdb->query( $sql );
        if ( ! empty( $wpdb->last_error ) ) {
            echo $wpdb->last_error;
            throw new \Exception( $wpdb->last_error );
        }
    }

    private static function getChildLSI( $primary_word, $lsi, $depth )
    {

        $lsi = get_transient('childlsi' . $primary_word);

        if ( empty( $lsi ) ) {

            global $wpdb;
            //$wpdb = new AmazonDB( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );

            $lsi = array();

            $primary_word = str_replace(array('<b>', '</b>'), array('', ''), $primary_word);

            $sql = $wpdb->prepare(
                "SELECT `lsi` FROM `wp_amazon_lsi` where `primary_word` = '%s' AND DATEDIFF( NOW(), `last_updated` ) < 30",
                $primary_word
            );

            $records = $wpdb->get_results($sql);
            if ($depth < 3 && !empty($records)) {
                foreach ($records as $record) {
                    $lsi[] = $record->lsi;
                    $lsi = AmazonCache::getChildLSI($record->lsi, $lsi, $depth + 1);
                }
            }

            // Cache for 30 days.
            set_transient('childlsi' . $primary_word, $lsi, 3600 * 24 * 30);

        }

        return $lsi;
    }

    public static function getLSI( $primary_word ) {

        $lsi = get_transient('childlsi' . $primary_word);

        if ( empty($lsi) ) {

            global $wpdb;
            //$wpdb = new AmazonDB( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );

            $lsi = array();

            $sql = $wpdb->prepare(
                "SELECT `lsi` FROM `wp_amazon_lsi` where `primary_word` = '%s' AND DATEDIFF( NOW(), `last_updated` ) < 30",
                $primary_word
            );

            $records = $wpdb->get_results($sql);

            if (!empty($wpdb->last_error)) {
                echo $wpdb->last_error;
                throw new \Exception($wpdb->last_error);
            }

            if (!empty($records)) {
                foreach ($records as $record) {
                    $lsi[] = strip_tags($record->lsi);
                    // $lsi = AmazonCache::getChildLSI($record->lsi, $lsi, 1);
                }
            }
            
            // Cache for 30 days.
            set_transient('childlsi' . $primary_word, $lsi, 3600 * 24 * 30);

        }
        

        return $lsi;

    }

    /**
     * @param string $searchTerm
     * @return array Array of IAmazonProducts
     */
    public static function performCachedSearch($searchTerm, $searchType ) {

    	// @todo
//        $products = get_transient('search' . $searchTerm . $searchType);
	    $products = false;

        if (empty($products)) {

            global $conn;
            $amazondb = new AmazonDB( $conn);

            // Remove stale searches.
            $sql = $amazondb->prepare(
                "DELETE FROM `wp_amazon_search` WHERE DATEDIFF( NOW(), `search_date` )  > 1",
                ''
            );
	        $amazondb->query($sql);
            if (!empty($amazondb->last_error)) {
                echo $amazondb->last_error;
                throw new \Exception($amazondb->last_error);
            }

            $sql = $amazondb->prepare(
                "SELECT `searchTerm`, `AIN` FROM `wp_amazon_search` 
              WHERE `searchTerm` = '%s'
              AND `searchType` = '%s'
              AND DATEDIFF( NOW(), `search_date` )  < 1",
                $searchTerm,
                $searchType
            );

            $records = $amazondb->get_results($sql);
            if (!empty($amazondb->last_error)) {
                echo $amazondb->last_error;
                throw new \Exception($amazondb->last_error);
            }


            $products = array();


            if (!empty($records)) {
                /*
                foreach ($records as $record) {
                    $products[] = AmazonCache::getProduct($record->AIN, '');
                }
                */
                foreach ( AmazonCache::generate_products( $records ) as $record ) {
                    $products[] = $record;
                }

            } else {
                throw new \Exception('No products found.');
            }

            // Cache for 1 days.
	        // @todo
           // set_transient('search' . $searchTerm . $searchType, $products, 3600 * 24 * 1);

        }
        
        return $products;

    }

    public static function cacheSearch( $searchTerm, $products, $searchType ) {

        global $conn;

        $amazondb = new AmazonDB($conn);


        foreach( $products as $product ) {

            $sql = $amazondb->prepare(
                "INSERT IGNORE INTO `wp_amazon_search` (`searchTerm`, `AIN`, `searchType`) VALUES ('%s', '%s', '%s');",
                $searchTerm,
                $product->getAsin(),
                $searchType
            );

	        $amazondb->query( $sql );
            $error = $amazondb->last_error;
            if ( ! empty( $error ) ) {
                throw new \Exception( $error . $sql );
            }
        }

    }

    public static function getCategory( $categoryID )  {

    	// @todo
        // $amazonCategory = get_transient('category' . $categoryID);
	    $amazonCategory = false;

        if (empty($amazonCategory)) {

           global $conn;
           $amazondb = new AmazonDB( $conn );

            // Remove stale categories
            $sql = $amazondb->prepare(
                "DELETE FROM `wp_amazon_amazon_categories` where DATEDIFF( NOW(), `last_updated` ) > 1",
                ''
            );
	        $amazondb->query($sql);
            $error = $amazondb->last_error;
            if (!empty($error)) {
                throw new \Exception($error . $sql);
            }

            $sql = $amazondb->prepare(
                "SELECT `category_name`, `number_items` FROM `wp_amazon_amazon_categories` where `category_id` = '%s'",
                $categoryID
            );

            $record = $amazondb->get_row($sql);
            $error = $amazondb->last_error;
            if (!empty($error)) {
                throw new \Exception($error . $sql);
            }

            $ancestors = array(); // @TODO

            if (empty($record)) {
                throw new \Exception('Category not found');
            }

            $amazonCategory = new AmazonCategory(
                $categoryID,
                $record->category_name,
                $ancestors,
                empty($record->number_items * 1) ? -1 : $record->number_items * 1
            );

            // Cache for 1 days.
	        // @todo
           // set_transient('category' . $categoryID, $amazonCategory, 3600 * 24 * 1);

        }

        return $amazonCategory;

    }

    public static function cacheCategory( AmazonCategory $category ) {

        global $conn;

        $amazondb = new AmazonDB($conn);

        $sql = $amazondb->prepare(
            "INSERT IGNORE INTO `wp_amazon_amazon_categories` (`category_id`, `category_name`, `number_items`) 
              VALUES ('%s', '%s', '%s');",
            $category->get_category_id(),
            $category->get_category_name(),
            $category->get_number_of_items()
        );
	    $amazondb->query( $sql );
        $error = $amazondb->last_error;
        if ( ! empty( $error ) ) {
            throw new \Exception( $error . $sql );
        }

        $ancestors = $category->get_ancestor_categories();

        foreach( $ancestors as $ancestor ) {

            AmazonCache::cacheCategory( $ancestor );

            $sql = $amazondb->prepare(
                "INSERT IGNORE INTO `wp_amazon_categories_categories` (`categoryID`, `parentCategoryID`)
            VALUES ('%s', '%s');",
                $category->get_category_id(),
                $ancestor->get_category_id()
            );

	        $amazondb->query($sql);
            $error = $amazondb->last_error;
            if ( ! empty( $error ) ) {
                throw new \Exception( $error . $sql);
            }
        }
    }


    /**
     * Recursive function to get ancestor categories.
     *
     * @param array $ancestors
     * @param string $categoryID
     * @param int $depth
     * @return array
     * @throws \Exception
     */
    public static function getCategories($ancestors, $categoryID, $depth ) {

        global $wpdb;
        //$wpdb = new AmazonDB( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );

        $sql = $wpdb->prepare(
            "SELECT `categoryID`, `parentCategoryID`, `number_items`, `category_name` FROM `wp_amazon_categories_categories` , `wp_amazon_amazon_categories` where `wp_amazon_categories_categories`.`parentCategoryID` = `wp_amazon_amazon_categories`.`category_id` AND `categoryID` = '%s'",
            $categoryID
        );
        $records = $wpdb->get_results( $sql );
        if ( ! empty( $wpdb->last_error ) ) {
            echo $wpdb->last_error;
            throw new \Exception( $wpdb->last_error );
        }
        if ( !empty( $records ) ) {

            foreach( AmazonCache::get_categories_generator( $records, $depth, $ancestors ) as $category ) {
                $ancestors[] = $category;
            }
            /*
            foreach( $records as $record ) {
                if ( $depth < 3 ) {
                    $ancestors = AmazonCache::getAncestorCategories( $ancestors, $record->parentCategoryID, $depth + 1 );
                }
                $number_of_items = ! empty( $record->number_items ) ? $record->number_items : -1;
                $category = new AmazonCategory(
                    $record->parentCategoryID,
                    $record->category_name,
                    $ancestors,
                    $number_of_items * 1
                );
                $ancestors[] = $category;
            }
            */
        }

        return $ancestors;
    }


    public static function get_ancestor_rrfs( $rrfs, $ancestor_categories, $salesRank, $depth ) {
        foreach( $ancestor_categories as $category ) {
            $rrfs[ $category->get_category_id() ]  = $salesRank / $category->get_number_of_items();
            $ancestors = $category->get_ancestor_categories();
            if ( ! empty( $ancestors ) && $depth < 5 ) {
                $rrfs = AmazonCache::get_ancestor_rrfs($rrfs, $ancestors, $salesRank, $depth + 1 );
            }
        }
        return $rrfs;
    }

    public static function getProduct( $ain, $relationshipType = '' )  {

        // B00NQGP42Y
	    // @todo
       // $data = get_transient('product' . $ain);
        $data = false;

        if (empty($data)) {

        	global $conn;
            $amazonDB = new AmazonDB($conn);

            $ain_string = implode("','", explode(',', $ain));

            $sql = $amazonDB->prepare(
                "SELECT
                `AIN`, 
                `title`, 
                `last_updated`, 
                `detailPageURL`, 
                `salesRank`, 
                `author`, 
                `brand`, 
                `department`, 
                `color`, 
                `ean`, 
                `feature`,
                 `genre`, 
                 `isAdultProduct`, 
                 `isAutographed`, 
                 `isMemorabilia`, 
                 `label`, 
                 `publisher`, 
                 `listPriceAmount`, 
                 `listPriceCurrencyCode`, 
                 `listPriceFormattedPrice`, 
                 `manufacturer`, 
                 `productGroup`, 
                 `productTypeName`, 
                 `lowestNewPriceAmount`, 
                 `lowestNewPriceCurrencyCode`, 
                 `lowestNewPriceFormattedPrice`, 
                 `lowestUsedPriceAmount`, 
                 `lowestUsedPriceCurrencyCode`, 
                 `lowestUsedPriceFormattedPrice`, 
                 `lowestCollectiblePriceAmount`, 
                 `lowestCollectiblePriceCurrencyCode`,
                 `lowestCollectiblePriceFormattedPrice`,
                 `binding`,
                 `mpn`,
                 `merchant`,
                 `warranty`,
                 `amountSaved`,
                 `availability`,
                 `freeShippingMessage`,
                 `customerReview`,
                 `editorialReview`
                  FROM `wp_amazon_amazon_products`
                  WHERE `ain` IN ('$ain_string')
                  AND DATEDIFF(NOW(), `last_updated`)  < 1
                  AND `avgRRF` > -1",
                '');

            $records = $amazonDB->get_results($sql);


            if (!empty($amazonDB->last_error)) {
                echo $amazonDB->last_error;
                echo($sql);
                throw new \Exception($amazonDB->last_error);
            }

            if (empty($records)) {
                throw new \Exception('Product not found when searching cache - ' . $ain_string);
            }

            $products = array();

            foreach ($records as $product_record) {

                $similar_products = array();


                $sql = $amazonDB->prepare(
                    "SELECT `frequentlyBoughtTogetherAIN` FROM `wp_amazon_product_frequently_bought_together` where `ain` = '%s' AND `ain` <> `frequentlyBoughtTogetherAIN`",
                    $ain
                );
                $records = $amazonDB->get_results($sql);
                if (!empty($amazonDB->last_error)) {
                    echo $amazonDB->last_error;
                    throw new \Exception($amazonDB->last_error);
                }
                if (!empty($records)) {
                    foreach ($records as $record) {
                        if ($ain != $record->frequentlyBoughtTogetherAIN) {
                            //    $similar_product = AmazonProductSearch::itemSearch( $record->frequentlyBoughtTogetherAIN, '' );
                            $similar_product = new \stdClass();
                            $similar_product->ASIN = $record->frequentlyBoughtTogetherAIN;
                            $similar_product->Title = '';
                            $similar_products[] = $similar_product;
                        }
                    }
                }

                $categories = array();
                $rrfs = array();

                $sql = $amazonDB->prepare(
                    "SELECT `wp_amazon_product_categories`.`categoryID`, `wp_amazon_product_categories`.`categoryName`, `wp_amazon_product_categories`.`RRF`, `wp_amazon_amazon_categories`.`number_items` FROM `wp_amazon_product_categories`, `wp_amazon_amazon_categories` WHERE `ain` = '%s' AND `wp_amazon_product_categories`.`categoryID` = `wp_amazon_amazon_categories`.`category_id`",
                    $ain
                );
                $records = $amazonDB->get_results($sql);
                if (!empty($amazonDB->last_error)) {
                    echo $amazonDB->last_error;
                    throw new \Exception($wpdb->last_error);
                }
                if (!empty($records)) {
                    foreach ($records as $record) {
                        $ancestors = AmazonCache::getAncestorCategories(array(), $record->categoryID, 1);
                        $number_of_items = !empty($record->number_items) ? $record->number_items : -1;
                        $category = new AmazonCategory(
                            $record->categoryID,
                            $record->categoryName,
                            $ancestors,
                            $number_of_items * 1
                        );
                        $categories[] = $category;
                    }
                }

                $salesRank = $product_record->salesRank * 1;

                foreach ($categories as $category) {
                    $rrfs[$category->get_category_id()] = $salesRank / $category->get_number_of_items();
                    $ancestors = $category->get_ancestor_categories();
                    $rrfs = AmazonCache::get_ancestor_rrfs($rrfs, $ancestors, $salesRank, 1);
                }

                $item_links = array();
                $sql = $amazonDB->prepare(
                    "SELECT `link` FROM `wp_amazon_product_links` where `ain` = '%s'",
                    $ain
                );
                $records = $amazonDB->get_results($sql);
                if (!empty($amazonDB->last_error)) {
                    echo $amazonDB->last_error;
                    throw new \Exception($wpdb->last_error);
                }
                if (!empty($records)) {
                    foreach ($records as $record) {
                        $item_links[] = $record->link;
                    }
                }

                $languages = array();
                $sql = $amazonDB->prepare(
                    "SELECT `language` FROM `wp_amazon_product_languages` where `ain` = '%s'",
                    $ain
                );
                $records = $amazonDB->get_results($sql);
                if (!empty($amazonDB->last_error)) {
                    echo $amazonDB->last_error;
                    throw new \Exception($wpdb->last_error);
                }
                if (!empty($records)) {
                    foreach ($records as $record) {
                        $languages[] = $record->language;
                    }
                }

                $images = array();
                $sql = $amazonDB->prepare(
                    "SELECT `src`, `height`, `width`, `type` FROM `wp_amazon_product_images` where `ain` = '%s'",
                    $ain
                );
                $records = $amazonDB->get_results($sql);
                if (!empty($amazonDB->last_error)) {
                    echo $amazonDB->last_error;
                    throw new \Exception($wpdb->last_error);
                }
                if (!empty($records)) {
                    foreach ($records as $record) {
                        $images[$record->type] = array(
                            'src' => $record->src,
                            'height' => $record->height,
                            'width' => $record->width
                        );
                    }
                }

                $features = array();
                $sql = $amazonDB->prepare(
                    "SELECT `feature` FROM `wp_amazon_product_features` where `ain` = '%s'",
                    $ain
                );
                $records = $amazonDB->get_results($sql);
                if (!empty($amazonDB->last_error)) {
                    echo $amazonDB->last_error;
                    throw new \Exception($wpdb->last_error);
                }
                if (!empty($records)) {
                    foreach ($records as $record) {
                        $features[] = $record->feature;
                    }
                }

                $dimensions = array();
                $sql = $amazonDB->prepare(
                    "SELECT `width`, `height`, `depth`, `weight` FROM `wp_amazon_item_dimensions` where `ain` = '%s'",
                    $ain
                );
                $record = $amazonDB->get_row($sql);
                if (!empty($amazonDB->last_error)) {
                    echo $amazonDB->last_error;
                    throw new \Exception($amazonDB->last_error);
                }
                if (!empty($record)) {
                    $dimensions['Height'] = $record->height;
                    $dimensions['Width'] = $record->width;
                    $dimensions['Length'] = $record->depth;
                    $dimensions['Weight'] = $record->weight;
                }

                $image_sets = array();
                $sql = $amazonDB->prepare(
                    "SELECT `AIN`, `type`, `height`, `width`, `url` FROM `wp_amazon_product_image_sets` where `AIN` = '%s'",
                    $ain
                );
                $records = $amazonDB->get_results($sql);
                if (!empty($amazonDB->last_error)) {
                    echo $amazonDB->last_error;
                    throw new \Exception($wpdb->last_error);
                }
                if (!empty($records)) {
                    foreach ($records as $record) {
                        $image_sets[] = array(
                            $record->type=>array(
                                'height'=>$record->height,
                                'width'=>$record->width,
                                'url'=>$record->url
                            )
                        );
                    }
                }


                $product = new AmazonProduct(
                    (string)$product_record->AIN,
                    (string)$product_record->detailPageURL,
                    $item_links,
                    (int)$product_record->salesRank,
                    (string)$product_record->author,
                    (string)$product_record->binding,
                    (string)$product_record->brand,
                    (string)$product_record->department,
                    (string)$product_record->color,
                    (string)$product_record->ean,
                    $features,
                    (string)$product_record->genre,
                    (bool)$product_record->isAdultProduct == 1,
                    (bool)$product_record->isAutographed == 1,
                    (bool)$product_record->isMemorabilia == 1,
                    $dimensions,
                    (string)$product_record->label,
                    $languages,
                    (string)$product_record->listPriceAmount,
                    (string)$product_record->listPriceCurrencyCode,
                    (string)$product_record->listPriceFormattedPrice,
                    (string)$product_record->manufacturer,
                    (string)$product_record->productGroup,
                    (string)$product_record->productTypeName,
                    (string)$product_record->publisher,
                    (string)$product_record->title,
                    (string)$product_record->lowestNewPriceAmount,
                    (string)$product_record->lowestNewPriceCurrencyCode,
                    (string)$product_record->lowestUsedPriceFormattedPrice,
                    (string)$product_record->lowestUsedPriceAmount,
                    (string)$product_record->lowestUsedPriceCurrencyCode,
                    (string)$product_record->lowestUsedPriceFormattedPrice,
                    (string)$product_record->lowestCollectiblePriceAmount,
                    (string)$product_record->lowestCollectiblePriceCurrencyCode,
                    (string)$product_record->lowestCollectiblePriceFormattedPrice,
                    $images,
                    $similar_products,
                    $categories,
                    $rrfs,
                    (string)$product_record->mpn,
                    (string)$product_record->merchant,
                    (string)$product_record->warranty,
                    $image_sets,
                    (float) $product_record->amountSaved,
                    (string) $product_record->availability,
                    (string) $product_record->freeShippingMessage,
                    (string) $product_record->customerReview,
                    (string) $product_record->editorialReview
                );

                $products[] = $product;

            }


            $data = count($products) == 1 ? $products[0] : $products;

            // Cache for 1 days.
	       // @todo
//            set_transient('product' . $ain, $data, 3600 * 24 * 1);
        }

        return $data;

    }

    public static function cacheProduct( IAmazonProduct $product, $relationshipType, $related_products = array() ) {


        global $conn;
        $amazondb = new AmazonDB($conn);

        $sql = $amazondb->prepare(
            "INSERT INTO `wp_amazon_amazon_products` (`AIN`, `title`, `last_updated`, `detailPageURL`, `salesRank`, `author`, `brand`, `department`, `color`, `ean`, `feature`, `genre`, `isAdultProduct`, `isAutographed`, `isMemorabilia`, `label`, `publisher`, `listPriceAmount`, `listPriceCurrencyCode`, `listPriceFormattedPrice`, `manufacturer`, `productGroup`, `productTypeName`, `lowestNewPriceAmount`, `lowestNewPriceCurrencyCode`, `lowestNewPriceFormattedPrice`, `lowestUsedPriceAmount`, `lowestUsedPriceCurrencyCode`, `lowestUsedPriceFormattedPrice`, `lowestCollectiblePriceAmount`, `lowestCollectiblePriceCurrencyCode`, `lowestCollectiblePriceFormattedPrice`, `binding`,
`avgRRF`, `mpn`, `merchant`, `warranty`, `amountSaved`, `availability`, `freeShippingMessage`, `customerReview`, `editorialReview`) VALUES ('%s', '%s', CURRENT_TIMESTAMP, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
            ON DUPLICATE KEY UPDATE
            `title` = '%s', 
            `last_updated` = CURRENT_TIMESTAMP, 
            `detailPageURL` = '%s', 
            `salesRank` = '%s', 
            `author` = '%s', 
            `brand` = '%s', 
            `department` = '%s', 
            `color` = '%s', 
            `ean` = '%s', 
            `feature` = '%s', 
            `genre` = '%s', 
            `isAdultProduct` = '%s', 
            `isAutographed` = '%s', 
            `isMemorabilia` = '%s', 
            `label` = '%s', 
            `publisher` = '%s', 
            `listPriceAmount` = '%s', 
            `listPriceCurrencyCode` = '%s', 
            `listPriceFormattedPrice` = '%s', 
            `manufacturer` = '%s', 
            `productGroup` = '%s', 
            `productTypeName` = '%s', 
            `lowestNewPriceAmount` = '%s', 
            `lowestNewPriceCurrencyCode` = '%s', 
            `lowestNewPriceFormattedPrice` = '%s', 
            `lowestUsedPriceAmount` = '%s', 
            `lowestUsedPriceCurrencyCode` = '%s', 
            `lowestUsedPriceFormattedPrice` = '%s', 
            `lowestCollectiblePriceAmount` = '%s', 
            `lowestCollectiblePriceCurrencyCode` = '%s',
            `lowestCollectiblePriceFormattedPrice` = '%s',
            `binding` = '%s',
            `avgRRF` = '%s',
            `mpn`='%s',
            `merchant`='%s',
            `warranty`='%s',
            `amountSaved` = '%s',
            `availability` = '%s',
            `freeShippingMessage` = '%s',
            `customerReview` = '%s',
            `editorialReview` = '%s'",
            $product->getAsin(),
            $product->getTitle(),
            $product->getDetailPageURL(),
            $product->getSalesRank(),
            $product->getAuthor(),
            $product->getBrand(),
            $product->getDepartment(),
            $product->getColor(),
            $product->getEAN(),
            '',
            $product->getGenre(),
            $product->isIsAdultProduct(),
            $product->isIsAutographed(),
            $product->isIsMemorabilia(),
            $product->getLabel(),
            $product->getPublisher(),
            $product->getListPriceAmount(),
            $product->getListPriceCurrencyCode(),
            $product->getListPriceFormattedPrice(),
            $product->getManufacturer(),
            $product->getProductGroup(),
            $product->getProductTypeName(),
            $product->getLowestNewPriceAmount(),
            $product->getLowestCollectiblePriceCurrencyCode(),
            $product->getLowestNewPriceFormattedPrice(),
            $product->getLowestUsedPriceAmount(),
            $product->getLowestUsedPriceCurrencyCode(),
            $product->getLowestUsedPriceFormattedPrice(),
            $product->getLowestCollectiblePriceAmount(),
            $product->getLowestCollectiblePriceCurrencyCode(),
            $product->getLowestCollectiblePriceFormattedPrice(),
            $product->getType(),
            $product->getAvgRRF(),
            $product->getMpn(),
            $product->getMerchant(),
            $product->getWarranty(),

            $product->getTitle(),
            $product->getDetailPageURL(),
            $product->getSalesRank(),
            $product->getAuthor(),
            $product->getBrand(),
            $product->getDepartment(),
            $product->getColor(),
            $product->getEAN(),
            '',
            $product->getGenre(),
            $product->isIsAdultProduct(),
            $product->isIsAutographed(),
            $product->isIsMemorabilia(),
            $product->getLabel(),
            $product->getPublisher(),
            $product->getListPriceAmount(),
            $product->getListPriceCurrencyCode(),
            $product->getListPriceFormattedPrice(),
            $product->getManufacturer(),
            $product->getProductGroup(),
            $product->getProductTypeName(),
            $product->getLowestNewPriceAmount(),
            $product->getLowestCollectiblePriceCurrencyCode(),
            $product->getLowestNewPriceFormattedPrice(),
            $product->getLowestUsedPriceAmount(),
            $product->getLowestUsedPriceCurrencyCode(),
            $product->getLowestUsedPriceFormattedPrice(),
            $product->getLowestCollectiblePriceAmount(),
            $product->getLowestCollectiblePriceCurrencyCode(),
            $product->getLowestCollectiblePriceFormattedPrice(),
            $product->getType(),
            (float) $product->getAvgRRF(),
            $product->getMpn(),
            $product->getMerchant(),
            $product->getWarranty(),
            
            $product->getAmountSaved(),
            $product->getAvailability(),
            $product->getFreeShippingMessage(),
            $product->getCustomerReview(),
            $product->getEditorialReview(),

            $product->getAmountSaved(),
            $product->getAvailability(),
            $product->getFreeShippingMessage(),
            $product->getCustomerReview(),
            $product->getEditorialReview()
            
        );


	    $amazondb->query( $sql );
        if ( ! empty( $amazondb->last_error ) ) {
            echo $amazondb->last_error;
            echo $sql;
            throw new \Exception( $amazondb->last_error );
        }

        $product_links = $product->getItemLinks();
        // Remove stale records.
        $sql = $amazondb->prepare(
            "DELETE FROM `wp_amazon_product_links` where DATEDIFF( NOW(), `last_updated` ) > 60",
            ''
        );
	    $amazondb->query( $sql );
        if ( ! empty( $amazondb->last_error ) ) {
            echo $amazondb->last_error;
            throw new \Exception( $amazondb->last_error );
        }
        foreach( $product_links as $link ) {
            $sql = $amazondb->prepare(
                "INSERT INTO `wp_amazon_product_links` (`ain`, `link`) VALUES ('%s', '%s')
                  ON DUPLICATE KEY UPDATE `link` = '%s';",
                $product->getAsin(),
                $link,
                $link
            );
	        $amazondb->query( $sql );
            if ( ! empty( $amazondb->last_error ) ) {
                echo $amazondb->last_error;
                throw new \Exception( $amazondb->last_error );
            }
        }

        $languages = $product->getLanguages();
        foreach( $languages as $language ) {
            $sql = $amazondb->prepare(
                "INSERT IGNORE INTO `wp_amazon_product_languages` (`ain`, `language`) VALUES ('%s', '%s');",
                $product->getAsin(),
                (string) is_object($language)?$language->Name:"Unknown"
            );
	        $amazondb->query( $sql );
            if ( ! empty( $amazondb->last_error ) ) {
                echo $amazondb->last_error;
                throw new \Exception( $amazondb->last_error );
            }
        }

        if ( !empty( $related_products ) ) {
            //$related_products = $product->related_products($relationshipType);
            foreach ($related_products as $related_product) {
                $sql = $amazondb->prepare(
                    "INSERT INTO `wp_amazon_product_related_products` (`ain`, `relatedProductAIN`, `relationshipType`) VALUES ('%s', '%s', '%s');",
                    $product->getAsin(),
                    $related_product->getAsin(),
                    $relationshipType
                );
	            $amazondb->query($sql);
                if (!empty($amazondb->last_error)) {
                    echo $amazondb->last_error;
                    throw new \Exception($amazondb->last_error);
                }
            }
        }


        $frequently_bought_together = $product->getSimilarProducts();
        // Remove stale bundles.
        // DELETE bundle records that are more than 3 days old.
        $sql = $amazondb->prepare(
            "DELETE FROM `wp_amazon_product_frequently_bought_together` 
             WHERE DATEDIFF( NOW(), `last_updated` ) > 3",
            ''
        );
	    $amazondb->query( $sql );
        if ( ! empty( $amazondb->last_error ) ) {
            echo $amazondb->last_error;
            throw new \Exception( $amazondb->last_error );
        }
        foreach( $frequently_bought_together as $frequently_bought_together_product ) {
            $sql = $amazondb->prepare(
                "INSERT IGNORE INTO `wp_amazon_product_frequently_bought_together` (`ain`, `frequentlyBoughtTogetherAin`) VALUES ('%s', '%s');",
                $product->getAsin(),
                (string) $frequently_bought_together_product->ASIN
            );
	        $amazondb->query( $sql );
            if ( ! empty( $amazondb->last_error ) ) {
                echo $amazondb->last_error;
                throw new \Exception( $amazondb->last_error );
            }
        }

        $images = $product->getImages();
        // Remove stale images.
        // Delete images that are more than a week old.
        $sql = $amazondb->prepare(
            "DELETE FROM `wp_amazon_product_images` where DATEDIFF( NOW(), `last_updated` ) > 7;",
            ''
        );
	    $amazondb->query( $sql );
        if ( ! empty( $amazondb->last_error ) ) {
            echo $amazondb->last_error;
            throw new \Exception( $amazondb->last_error );
        }
        foreach( $images as $type=>$image ) {
            $sql = $amazondb->prepare(
                "INSERT INTO `wp_amazon_product_images` (`type`, `ain`, `src`, `height`, `width`) VALUES ('%s', '%s', '%s', '%s', '%s')
 ON DUPLICATE KEY UPDATE `type` = '%s', `src`='%s', `height`='%s', `width`='%s';",
                $type,
                $product->getAsin(),
                (string) isset($image['src'])?$image['src']:$image['url'],
                (int) $image['height'],
                (int) $image['width'],
                $type,
                (string) isset($image['src'])?$image['src']:$image['url'],
                (int) $image['height'],
                (int) $image['width']
            );
	        $amazondb->query( $sql );
            if ( ! empty( $amazondb->last_error ) ) {
                echo $amazondb->last_error;
                throw new \Exception( $amazondb->last_error );
            }
        }

        $features = $product->getFeature();
        // Delete stale features.
        $sql = $amazondb->prepare(
            "DELETE FROM `wp_amazon_product_features` where DATEDIFF( NOW(), `last_updated` ) > 7",
            ''
        );
	    $amazondb->query( $sql );
        if ( ! empty( $amazondb->last_error ) ) {
            echo $amazondb->last_error;
            throw new \Exception( $amazondb->last_error );
        }
        foreach( $features as $feature ) {
            $sql = $amazondb->prepare(
                "INSERT INTO `wp_amazon_product_features` (`ain`, `feature`) VALUES ('%s', '%s')
 ON DUPLICATE KEY UPDATE `feature` = '%s';",
                $product->getAsin(),
                $feature,
                $feature
            );
	        $amazondb->query( $sql );
            if ( ! empty( $amazondb->last_error ) ) {
                echo $amazondb->last_error;
                throw new \Exception( $amazondb->last_error );
            }
        }

        $dimensions = $product->getItemDimensions();

        if (empty($dimensions)) {
        	$dimensions = array(
        		'Width'=>'',
		        'Height'=>'',
		        'Length'=>'',
		        'Weight'=>''
	        );
        }


        $sql = $amazondb->prepare(
            "INSERT IGNORE INTO `wp_amazon_item_dimensions` (`ain`, `width`, `height`, `depth`, `weight`) VALUES ('%s', '%s', '%s', '%s', '%s');",
            $product->getAsin(),
	        isset($dimensions['Width'])?$dimensions['Width']:'',
	        isset($dimensions['Height'])?$dimensions['Height']:'',
	        isset($dimensions['Length'])?$dimensions['Length']:'',
            isset($dimensions['Weight'])?$dimensions['Weight']:''
        );
	    $amazondb->query( $sql );
        if ( ! empty( $amazondb->last_error ) ) {
            echo $amazondb->last_error;
            throw new \Exception( $amazondb->last_error );
        }

        $categories = $product->getCategories();
        // Remove records that are more than a month old
        $sql = $amazondb->prepare(
            "DELETE FROM `wp_amazon_product_categories` WHERE DATEDIFF( NOW(), `last_updated` ) > 30;",
            ''
        );
	    $amazondb->query( $sql );
        if ( ! empty( $wpdb->last_error ) ) {
            echo $wpdb->last_error;
            throw new \Exception( $amazondb->last_error );
        }
        foreach( $categories as $category ) {
            $sql = $amazondb->prepare(
                "INSERT IGNORE INTO `wp_amazon_product_categories` 
              (`AIN`, `categoryID`, `RRF`)
               VALUES ('%s', '%s', '%s' );",
                $product->getAsin(),
                $category->get_category_id(),
                $product->getSalesRank() / $category->get_number_of_items()
            );
	        $amazondb->query( $sql );
            if ( ! empty( $amazondb->last_error ) ) {
                echo $amazondb->last_error;
                throw new \Exception( $amazondb->last_error );
            }
        }


        $image_sets = $product->getImageSets();
        // Remove records that are more than a month old
        $sql = $amazondb->prepare(
            "DELETE FROM `wp_amazon_product_image_sets` WHERE DATEDIFF( NOW(), `last_updated` ) > 30;",
            ''
        );
	    $amazondb->query( $sql );
        if ( ! empty( $amazondb->last_error ) ) {
            echo $amazondb->last_error;
            throw new \Exception( $amazondb->last_error );
        }
        
        if ( !empty( $image_sets ) ) {
            foreach ($image_sets as $image_set) {

                $sql = $amazondb->prepare(
                    "INSERT INTO `wp_amazon_product_image_sets` (`AIN`, `type`, `height`, `width`, `url`, `last_updated`) VALUES ('%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `height`='%s', `width`='%s', `url` = '%s', `last_updated` = '%s';",
                    $product->getAsin(),
                    'swatch',
                    $image_set['swatch']['height'],
                    $image_set['swatch']['width'],
                    $image_set['swatch']['url'],
                    $image_set['hires']['height'],
                    $image_set['hires']['width'],
                    $image_set['hires']['url'],
                    date("Y-m-d H:i:s")
                );
	            $amazondb->query($sql);

                $sql = $amazondb->prepare(
                    "INSERT INTO `wp_amazon_product_image_sets` (`AIN`, `type`, `height`, `width`, `url`, `last_updated`) VALUES ('%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `height`='%s', `width`='%s', `url` = '%s', `last_updated` = '%s';",
                    $product->getAsin(),
                    'small',
                    $image_set['small']['height'],
                    $image_set['small']['width'],
                    $image_set['small']['url'],
                    $image_set['hires']['height'],
                    $image_set['hires']['width'],
                    $image_set['hires']['url'],
                    date("Y-m-d H:i:s")
                );
	            $amazondb->query($sql);

                $sql = $amazondb->prepare(
                    "INSERT INTO `wp_amazon_product_image_sets` (`AIN`, `type`, `height`, `width`, `url`, `last_updated`) VALUES ('%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `height`='%s', `width`='%s', `url` = '%s', `last_updated` = '%s';",
                    $product->getAsin(),
                    'thumb',
                    $image_set['thumb']['height'],
                    $image_set['thumb']['width'],
                    $image_set['thumb']['url'],
                    $image_set['hires']['height'],
                    $image_set['hires']['width'],
                    $image_set['hires']['url'],
                    date("Y-m-d H:i:s")
                );
	            $amazondb->query($sql);

                $sql = $amazondb->prepare(
                    "INSERT INTO `wp_amazon_product_image_sets` (`AIN`, `type`, `height`, `width`, `url`, `last_updated`) VALUES ('%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `height`='%s', `width`='%s', `url` = '%s', `last_updated` = '%s';",
                    $product->getAsin(),
                    'medium',
                    $image_set['medium']['height'],
                    $image_set['medium']['width'],
                    $image_set['medium']['url'],
                    $image_set['hires']['height'],
                    $image_set['hires']['width'],
                    $image_set['hires']['url'],
                    date("Y-m-d H:i:s")
                );
	            $amazondb->query($sql);

                $sql = $amazondb->prepare(
                    "INSERT INTO `wp_amazon_product_image_sets` (`AIN`, `type`, `height`, `width`, `url`, `last_updated`) VALUES ('%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `height`='%s', `width`='%s', `url` = '%s', `last_updated` = '%s';",
                    $product->getAsin(),
                    'tiny',
                    $image_set['tiny']['height'],
                    $image_set['tiny']['width'],
                    $image_set['tiny']['url'],
                    $image_set['hires']['height'],
                    $image_set['hires']['width'],
                    $image_set['hires']['url'],
                    date("Y-m-d H:i:s")
                );
	            $amazondb->query($sql);

                $sql = $amazondb->prepare(
                    "INSERT INTO `wp_amazon_product_image_sets` (`AIN`, `type`, `height`, `width`, `url`, `last_updated`) VALUES ('%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `height`='%s', `width`='%s', `url` = '%s', `last_updated` = '%s';",
                    $product->getAsin(),
                    'large',
                    $image_set['large']['height'],
                    $image_set['large']['width'],
                    $image_set['large']['url'],
                    $image_set['hires']['height'],
                    $image_set['hires']['width'],
                    $image_set['hires']['url'],
                    date("Y-m-d H:i:s")
                );
	            $amazondb->query($sql);

	            $sql = $amazondb->prepare(
                    "INSERT INTO `wp_amazon_product_image_sets` (`AIN`, `type`, `height`, `width`, `url`, `last_updated`) VALUES ('%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `height`='%s', `width`='%s', `url` = '%s', `last_updated` = '%s';",
                    $product->getAsin(),
                    'hires',
                    $image_set['hires']['height'],
                    $image_set['hires']['width'],
                    $image_set['hires']['url'],
                    $image_set['hires']['height'],
                    $image_set['hires']['width'],
                    $image_set['hires']['url'],
                    date("Y-m-d H:i:s")
                );
	            $amazondb->query($sql);

                if (!empty($amazondb->last_error)) {
                    echo $amazondb->last_error;
                    throw new \Exception($amazondb->last_error);
                }

            } // for each image set
            // var_dump('jjjj');
            //die();


            $pd = AmazonCache::getProduct($product->getAsin());
            //var_dump( $pd );
            //die();
        }

        return true;
    }

    public static function getKeywords( $phrase, $rrf, $depth) {

    	// @todo
        //$data = get_transient('keywords' . $phrase . $rrf);
	    $data = false;

        if ($data == false) {

            global $conn;
            $amazondb = new AmazonDB($conn);

            // Delete old records.
            $sql = $amazondb->prepare(
                "DELETE FROM wp_amazon_keywords WHERE DATEDIFF( NOW(), `last_updated` ) > 1",
                ''
            );
	        $amazondb->query($sql);
            if (!empty($amazondb->error)) {
                throw new \Exception($amazondb->error);
            }

            $sql = $amazondb->prepare(
                "SELECT `phrase`,`variation` FROM wp_amazon_keywords WHERE `phrase` = '%s'",
                $phrase
            );
            $records = $amazondb->get_results($sql);
            if (!empty($amazondb->error)) {
                throw new \Exception($amazondb->error);
            }

            if (!empty($records)) {
                foreach ($records as $record) {
                    if ( $depth < 2 && $record->phrase != $record->variation ) {
                        $variations[ $record->variation ] = AmazonCache::getKeywords( $record->variation, $rrf, $depth + 1 );
                    }
                }
            }

            $categories = array();
            $sql = $amazondb->prepare(
                "SELECT `phrase`,`category_name` FROM `wp_amazon_keywords_categories` WHERE `phrase` = '%s'",
                $phrase
            );
            $cat_records = $amazondb->get_results($sql);
            foreach ($cat_records as $cat_record) {
                $categories[] = $cat_record->category_name;
            }

            if (!isset($variations)) {
            	$variations = array();
            }

            $keyword = new AmazonKeyword($phrase, $categories, $variations, $rrf);


            //      var_dump( $keyword );
//
            $data = array($phrase => $keyword);

            // Cache for 1 days.
	        // @todo
            // set_transient('keywords' . $phrase . $rrf, $data, 3600 * 24 * 1);

        }

        return $data;

    }

    public static function cacheKeywords( $keyword ) {
        global $conn;

        $amazondb = new AmazonDB($conn);

        if ( !empty( $keyword->categories ) ) {
            foreach ($keyword->categories as $category) {
                $sql = $amazondb->prepare(
                    "INSERT IGNORE INTO `wp_amazon_keywords_categories` (`phrase`, `category_name`) VALUES ('%s', '%s');",
                    $keyword->name,
                    $category->name
                );
	            $amazondb->query($sql);
                if (!empty($amazondb->error)) {
                    throw new \Exception($wpdb->error);
                }
            }
        }

        if ( !empty( $keyword->variations ) ) {
            foreach ($keyword->variations as $name=>$variation) {
                $sql = $amazondb->prepare(
                    "INSERT INTO `wp_amazon_keywords` (`phrase`, `variation`, `last_updated`) VALUES ('%s', '%s', CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE `phrase`='%s', `variation`='%s', `last_updated` = '%s';",
                    $keyword->name,
                    $name,
                    $keyword->name,
                    $name,
                    date( "Y-m-d H:i:s" )
                );
	            $amazondb->query($sql);
                if (!empty($amazondb->error)) {
                    throw new \Exception($amazondb->error);
                }
            }
        }


    }
    
}