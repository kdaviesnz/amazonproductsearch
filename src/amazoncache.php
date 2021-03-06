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
            yield AmazonCache::getProduct($record->keyValue, '');
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

        global $conn;
        $amazondb = new AmazonDB($conn );

	    $transient = new Transient($conn);
	    $related_products = $transient->fetch('related' . $product->getAsin() );

        if ( $related_products == false ) {

            $related_products = array();
            $relatedASINs = array();

            $sql = $amazondb->prepare(
                "SELECT `relatedAIN` FROM `wp_amazon_related_products` 
            WHERE `AIN` = '%s' and `relationshipType`='%s'
            AND DATEDIFF( NOW(), `lastUpdated` ) < 7",
                $product->getAsin(),
                $relationshipType
            );
            $records = $amazondb->get_results($sql);
            if (!empty($amazondb->last_error)) {
                echo $amazondb->last_error;
                throw new \Exception($amazondb->last_error);
            }
            if (!empty($records)) {
                foreach (AmazonCache::related_products_generator() as $relatedAIN) {
                    $relatedASINs[] = $relatedAIN;
                }
                $related_products = AmazonAmazonProductSearch::itemSearch(implode(',', $relatedASINs), 'ASIN', $relationshipType);
            } else {
                throw new \Exception('No records found');
            }

            // Cache for 7 days.
	        $transient->save('related_' . $product->getAsin(), $related_products, 3600 * 24 * 7);

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
	    $transient = new Transient($conn);
	    $ancestors =$transient->fetch('ancestors_' . $categoryID );

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

            $transient->save('ancestors_' . $categoryID, $ancestors, 3600 ); // Cache for one hour
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

	    global $conn;

	    $transient = new Transient($conn);
	    $lsi = $transient->fetch('childlsi' . $primary_word);

        if ( empty( $lsi ) ) {

            $amazondb = new AmazonDB( $conn );

            $lsi = array();

            $primary_word = str_replace(array('<b>', '</b>'), array('', ''), $primary_word);

            $sql = $amazondb->prepare(
                "SELECT `lsi` FROM `wp_amazon_lsi` where `primary_word` = '%s' AND DATEDIFF( NOW(), `last_updated` ) < 30",
                $primary_word
            );

            $records = $amazondb->get_results($sql);
            if ($depth < 3 && !empty($records)) {
                foreach ($records as $record) {
                    $lsi[] = $record->lsi;
                    $lsi = AmazonCache::getChildLSI($record->lsi, $lsi, $depth + 1);
                }
            }

            // Cache for 30 days.
	        $transient->save('childlsi' . $primary_word, $lsi, 3600 * 24 * 30);

        }

        return $lsi;
    }

    public static function getLSI( $primary_word ) {

	    global $conn;
	    $transient = new Transient($conn);
	    $lsi = $transient->fetch('childlsi' . $primary_word);

        if ( empty($lsi) ) {

            $amazondb = new AmazonDB( $conn );

            $lsi = array();

            $sql = $amazondb->prepare(
                "SELECT `lsi` FROM `wp_amazon_lsi` where `primary_word` = '%s' AND DATEDIFF( NOW(), `last_updated` ) < 30",
                $primary_word
            );

            $records = $amazondb->get_results($sql);

            if (!empty($amazondb->last_error)) {
                echo $amazondb->last_error;
                throw new \Exception($amazondb->last_error);
            }

            if (!empty($records)) {
                foreach ($records as $record) {
                    $lsi[] = strip_tags($record->lsi);
                    // $lsi = AmazonCache::getChildLSI($record->lsi, $lsi, 1);
                }
            }
            
            // Cache for 30 days.
	        $transient->save('childlsi' . $primary_word, $lsi, 3600 * 24 * 30);

        }
        

        return $lsi;

    }

    /**
     * @param string $searchTerm
     * @return array Array of IAmazonProducts
     */
    public static function performCachedSearch($searchPhrase, $searchIndex ) {

	    global $conn;
	 //   $transient = new Transient($conn);
      //  $products = $transient->fetch('search' . $searchTerm . $searchType);
	    $amazondb = new AmazonDB( $conn);

	    $searchPhraseSafe = mysqli_real_escape_string($conn, $searchPhrase);
	    $searchIndexSafe = mysqli_real_escape_string($conn, $searchIndex);


	    $sql = "SELECT `wp_amazon_product_categories`.`productID`, `wp_amazon_amazon_products`.`keyType`, `wp_amazon_amazon_products`.`keyValue`
				FROM `wp_amazon_product_categories`, `wp_amazon_amazon_products`
				WHERE `wp_amazon_product_categories`.`searchPhrase` = '$searchPhraseSafe' 
				AND `wp_amazon_product_categories`.`searchIndex` = '$searchIndexSafe'
				AND `wp_amazon_product_categories`.`productID`= `wp_amazon_amazon_products`.`id`";

	    $rows = $amazondb->get_results($sql);

	    $products = array(
	        "items"=>array()
	    );

	    foreach ($rows as $row) {
	    	$product = AmazonCache::getProduct($row->keyValue);
		    $products["items"][] = AmazonCache::getProduct($row->keyValue);
	    }

        return $products;

    }

    public static function cacheSearch( $searchPhrase, $products, $searchIndex ) {

        global $conn;

        $amazondb = new AmazonDB($conn);
        $searchPhraseSafe = mysqli_real_escape_string($conn, $searchPhrase);
	    $searchIndexSafe = mysqli_real_escape_string($conn, $searchIndex);

        foreach( $products as $product ) {

        	$productIDSafe = mysqli_real_escape_string($conn, $product->getID());

            $sql = "INSERT IGNORE INTO `wp_amazon_product_categories` (`productID`, `searchPhrase`, `searchIndex`) VALUES ('$productIDSafe', '$searchPhraseSafe', '$searchIndexSafe');";

	        $amazondb->query( $sql );
            $error = $amazondb->last_error;
            if ( ! empty( $error ) ) {
                throw new \Exception( $error . $sql );
            }
        }

    }

    public static function getCategory( $categoryID )  {

	    global $conn;
	    $transient = new Transient($conn);
	    $amazonCategory = $transient->fetch('category' . $categoryID);

        if (empty($amazonCategory)) {

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
	        $transient->save('category' . $categoryID, $amazonCategory, 3600 * 24 * 1);

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

    public static function getProduct( $keyValue, $relationshipType = '' )  {

        // B00NQGP42Y
	    global $conn;
	    $transient = new Transient($conn);
	    $data = $transient->fetch('product' . $keyValue);

		$data = null;

        if (empty($data)) {


            $amazonDB = new AmazonDB($conn);

            $keyValue_string = implode("','", explode(',', $keyValue));

            $sql = $amazonDB->prepare(
                "SELECT
				`id`,
				`keyType`,
				`keyValue`,
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
                 `editorialReview`,
                 `T30days`,
                 `T6months`,
                 `T12months`,
                 `T30daysSalesCount`,
                 `T6monthsSalesCount`,
                 `T12monthsSalesCount`,
                 `soldByAmazon`,
                 `competitivePrice`,
                 `UPC`
                  FROM `wp_amazon_amazon_products`
                  WHERE `keyValue` IN ('$keyValue_string')
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
                throw new \Exception('Product not found when searching cache - ' . $keyValue_string);
            }

            $products = array();
	        $similar_products = array();

            foreach ($records as $product_record) {

                $sql = $amazonDB->prepare(
                    "SELECT `frequentlyBoughtTogether` FROM `wp_amazon_product_frequently_bought_together` where `keyValue` = '%s' AND `keyValue` <> `frequentlyBoughtTogether`",
                    $keyValue
                );
                $records = $amazonDB->get_results($sql);
                if (!empty($amazonDB->last_error)) {
                    echo $amazonDB->last_error;
                    throw new \Exception($amazonDB->last_error);
                }
                if (!empty($records)) {
                    foreach ($records as $record) {
                        if ($keyValue != $record->frequentlyBoughtTogether) {
                            //    $similar_product = AmazonProductSearch::itemSearch( $record->frequentlyBoughtTogetherAIN, 'ASIN', '' );
                            $similar_product = new \stdClass();
                            $similar_product->keyValue = $record->frequentlyBoughtTogether;
                            $similar_product->Title = '';
                            $similar_products[] = $similar_product;
                        }
                    }
                }

                $categories = array();
                $rrfs = array();

                /*
                $sql = $amazonDB->prepare(
                    "SELECT `wp_amazon_product_categories`.`categoryID`, `wp_amazon_product_categories`.`categoryName`, `wp_amazon_product_categories`.`RRF`, `wp_amazon_amazon_categories`.`number_items` FROM `wp_amazon_product_categories`, `wp_amazon_amazon_categories` WHERE `keyValue` = '%s' AND `wp_amazon_product_categories`.`categoryID` = `wp_amazon_amazon_categories`.`category_id`",
                    $keyValue
                );
                $records = $amazonDB->get_results($sql);
                if (!empty($amazonDB->last_error)) {
                    echo $amazonDB->last_error;
                    throw new \Exception($amazonDB->last_error);
                }
                */
                $records = array();
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
                    "SELECT `link` FROM `wp_amazon_product_links` where `keyValue` = '%s'",
                    $keyValue
                );
                $records = $amazonDB->get_results($sql);
                if (!empty($amazonDB->last_error)) {
                    echo $amazonDB->last_error;
                    throw new \Exception($amazonDB->last_error);
                }
                if (!empty($records)) {
                    foreach ($records as $record) {
                        $item_links[] = $record->link;
                    }
                }

                $languages = array();
                $sql = $amazonDB->prepare(
                    "SELECT `language` FROM `wp_amazon_product_languages` where `keyValue` = '%s'",
                    $keyValue
                );
                $records = $amazonDB->get_results($sql);
                if (!empty($amazonDB->last_error)) {
                    echo $amazonDB->last_error;
                    throw new \Exception($amazonDB->last_error);
                }
                if (!empty($records)) {
                    foreach ($records as $record) {
                        $languages[] = $record->language;
                    }
                }

                $images = array();
                $sql = $amazonDB->prepare(
                    "SELECT `src`, `height`, `width`, `type` FROM `wp_amazon_product_images` where `keyValue` = '%s'",
                    $keyValue
                );
                $records = $amazonDB->get_results($sql);
                if (!empty($amazonDB->last_error)) {
                    echo $amazonDB->last_error;
                    throw new \Exception($amazonDB->last_error);
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
                    "SELECT `feature` FROM `wp_amazon_product_features` where `keyValue` = '%s'",
                    $keyValue
                );
                $records = $amazonDB->get_results($sql);
                if (!empty($amazonDB->last_error)) {
                    echo $amazonDB->last_error;
                    throw new \Exception($amazonDB->last_error);
                }
                if (!empty($records)) {
                    foreach ($records as $record) {
                        $features[] = $record->feature;
                    }
                }

                $dimensions = array();
                $sql = $amazonDB->prepare(
                    "SELECT `width`, `height`, `depth`, `weight` FROM `wp_amazon_item_dimensions` where `keyValue` = '%s'",
                    $keyValue
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
                    "SELECT `keyValue`, `type`, `height`, `width`, `url` FROM `wp_amazon_product_image_sets` where `keyValue` = '%s'",
                    $keyValue
                );
                $records = $amazonDB->get_results($sql);
                if (!empty($amazonDB->last_error)) {
                    echo $amazonDB->last_error;
                    throw new \Exception($amazonDB->last_error);
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
	                $product_record->id,
                    $product_record->keyType,
	                $product_record->keyValue,
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
                    (string) $product_record->editorialReview,
	                (string) $product_record->UPC
                );


                $product->set30Days( empty($product_record->T30days)?0.00:$product_record->T30days );
	            $product->set6months(empty($product_record->T6months)?0.00: $product_record->T6months);
                $product->set12months(empty($product_record->T12months)?0.00: $product_record->T12months);
	            $product->set30DaysSalesCount(empty($product_record->T30daysSalesCount)?0:(int) $product_record->T30daysSalesCount);
	            $product->set6monthsSalesCount(empty($product_record->T6monthsSalesCount)?0:(int) $product_record->T6monthsSalesCount);
	            $product->set12monthsSalesCount(empty($product_record->T12monthsSalesCount)?0:(int) $product_record->T12monthsSalesCount);

	            $product->setCompetitivePrice(empty($product_record->competitivePrice)?0.00:$product_record->competitivePrice);

	            $product->setSoldByAmazon(empty($product_record->soldByAmazon)?false:$product_record->soldByAmazon==1);

	            $products[] = $product;

            }


            $data = count($products) == 1 ? $products[0] : $products;

            // Cache for 1 days.
	        $transient->save('product' . $keyValue, $data, 3600 * 24 * 1);
        }

        return $data;

    }

    public static function cacheProduct( IAmazonProduct $product, $relationshipType, $related_products = array() ) {


	    global $conn;
	    $amazondb = new AmazonDB( $conn );

	    // Search for matching product.
	    $keyTypeSafe  = mysqli_real_escape_string( $conn, $product->getKeyType() );
	    $keyValueSafe = mysqli_real_escape_string( $conn, $product->getKeyValue() );

	    $sql = "SELECT `id` FROM `wp_amazon_amazon_products` WHERE `keyValue` = '$keyValueSafe' AND `keyType` = '$keyTypeSafe'";

	    $results = $amazondb->get_results( $sql );

	    $titleSafe                                = mysqli_real_escape_string( $conn, $product->getTitle() );
	    $detailPageUrlSafe                        = mysqli_real_escape_string( $conn, $product->getDetailPageURL() );
	    $salesRankSafe                            = mysqli_real_escape_string( $conn, $product->getSalesRank() );
	    $authorSafe                               = mysqli_real_escape_string( $conn, $product->getAuthor() );
	    $brandSafe                                = mysqli_real_escape_string( $conn, $product->getBrand() );
	    $departmenSafe                            = mysqli_real_escape_string( $conn, $product->getDepartment() );
	    $colorSafe                                = mysqli_real_escape_string( $conn, $product->getColor() );
	    $eanSafe                                  = mysqli_real_escape_string( $conn, $product->getEAN() );
	    $genreSafe                                = mysqli_real_escape_string( $conn, $product->getGenre() );
	    $isAdultProductSafe                       = mysqli_real_escape_string( $conn, $product->isIsAdultProduct() );
	    $isAutographedSafe                        = mysqli_real_escape_string( $conn, $product->isIsAutographed() );
	    $isMemorabiliaSafe                        = mysqli_real_escape_string( $conn, $product->isIsMemorabilia() );
	    $labelSafe                                = mysqli_real_escape_string( $conn, $product->getLabel() );
	    $publisherSafe                            = mysqli_real_escape_string( $conn, $product->getPublisher() );
	    $listPriceAmountSafe                      = mysqli_real_escape_string( $conn, $product->getListPriceAmount() );
	    $listPriceCurrencyCodeSafe                = mysqli_real_escape_string( $conn, $product->getListPriceCurrencyCode() );
	    $listPriceFormattedPriceSafe              = mysqli_real_escape_string( $conn, $product->getListPriceFormattedPrice() );
	    $manufacturerSafe                         = mysqli_real_escape_string( $conn, $product->getManufacturer() );
	    $productGroupSafe                         = mysqli_real_escape_string( $conn, $product->getProductGroup() );
	    $productTypeNameSafe                      = mysqli_real_escape_string( $conn, $product->getProductTypeName() );
	    $lowestNewPriceAmountSafe                 = mysqli_real_escape_string( $conn, $product->getLowestNewPriceAmount() );
	    $lowestCollectiblePriceCurrencyCodeSafe   = mysqli_real_escape_string( $conn, $product->getLowestCollectiblePriceCurrencyCode() );
	    $lowestNewPriceFormattedPriceSafe         = mysqli_real_escape_string( $conn, $product->getLowestNewPriceFormattedPrice() );
	    $lowestUsedPriceAmountSafe                = mysqli_real_escape_string( $conn, $product->getLowestUsedPriceAmount() );
	    $lowestUsedPriceCurrencyCodeSafe          = mysqli_real_escape_string( $conn, $product->getLowestUsedPriceCurrencyCode() );
	    $lowestUsedPriceFormattedPriceSafe        = mysqli_real_escape_string( $conn, $product->getLowestUsedPriceFormattedPrice() );
	    $lowestCollectiblePriceAmountSafe         = mysqli_real_escape_string( $conn, $product->getLowestCollectiblePriceAmount() );
	    $lowestCollectiblePriceCurrencyCodeSafe   = mysqli_real_escape_string( $conn, $product->getLowestCollectiblePriceCurrencyCode() );
	    $lowestCollectiblePriceFormattedPriceSafe = mysqli_real_escape_string( $conn, $product->getLowestCollectiblePriceFormattedPrice() );
	    $typeSafe                                 = mysqli_real_escape_string( $conn, $product->getType() );
	    $avgRRFSafe                               = mysqli_real_escape_string( $conn, $product->getAvgRRF() );
	    $mpnSafe                                  = mysqli_real_escape_string( $conn, $product->getMpn() );
	    $merchantSafe                             = mysqli_real_escape_string( $conn, $product->getMerchant() );
	    $warrantySafe                             = mysqli_real_escape_string( $conn, $product->getWarranty() );
	    $amountSavedSafe                          = mysqli_real_escape_string( $conn, $product->getAmountSaved() );
	    $availabilitySafe                         = mysqli_real_escape_string( $conn, $product->getAvailability() );
	    $freeShippingMessageSafe                  = mysqli_real_escape_string( $conn, $product->getFreeShippingMessage() );
	    $customerReviewSafe                       = mysqli_real_escape_string( $conn, $product->getCustomerReview() );
	    $editorialReviewSafe                      = mysqli_real_escape_string( $conn, $product->getEditorialReview() );
	    $asinSafe                                 = mysqli_real_escape_string( $conn, $product->getAsin() );
	    $UPCSafe                                  = mysqli_real_escape_string( $conn, $product->getUPC() );

	    if ( ! empty( $results ) ) {
		    $id  = $results[0]->id;
		    $sql = $amazondb->prepare(
			    "UPDATE `wp_amazon_amazon_products` SET			    
            `title` = '$titleSafe', 
            `last_updated` = CURRENT_TIMESTAMP, 
            `detailPageURL` = '$detailPageUrlSafe', 
            `salesRank` = '$salesRankSafe', 
            `author` = '$authorSafe', 
            `brand` = '$brandSafe', 
            `department` = '$departmenSafe', 
            `color` = '$colorSafe', 
            `ean` = '$eanSafe', 
            `feature` = '', 
            `genre` = '$genreSafe', 
            `isAdultProduct` = '$isAdultProductSafe', 
            `isAutographed` = '$isAutographedSafe', 
            `isMemorabilia` = '$isMemorabiliaSafe', 
            `label` = '$labelSafe', 
            `publisher` = '$publisherSafe', 
            `listPriceAmount` = '$listPriceAmountSafe', 
            `listPriceCurrencyCode` = '$listPriceCurrencyCodeSafe', 
            `listPriceFormattedPrice` = '$listPriceFormattedPriceSafe', 
            `manufacturer` = '$manufacturerSafe', 
            `productGroup` = '$productGroupSafe', 
            `productTypeName` = '$productTypeNameSafe', 
            `lowestNewPriceAmount` = '$lowestNewPriceAmountSafe', 
            `lowestNewPriceCurrencyCode` = '$lowestCollectiblePriceCurrencyCodeSafe', 
            `lowestNewPriceFormattedPrice` = '$lowestNewPriceFormattedPriceSafe', 
            `lowestUsedPriceAmount` = '$lowestUsedPriceAmountSafe', 
            `lowestUsedPriceCurrencyCode` = '$lowestUsedPriceCurrencyCodeSafe', 
            `lowestUsedPriceFormattedPrice` = '$lowestUsedPriceFormattedPriceSafe', 
            `lowestCollectiblePriceAmount` = '$lowestCollectiblePriceAmountSafe', 
            `lowestCollectiblePriceCurrencyCode` = '$lowestCollectiblePriceCurrencyCodeSafe',
            `lowestCollectiblePriceFormattedPrice` = '$lowestCollectiblePriceFormattedPriceSafe',
            `binding` = '$typeSafe',
            `avgRRF` = '$avgRRFSafe',
            `mpn`='$mpnSafe',
            `merchant`='$merchantSafe',
            `warranty`='$warrantySafe',
            `amountSaved` = '$amountSavedSafe',
            `availability` = '$availabilitySafe',
            `freeShippingMessage` = '$freeShippingMessageSafe',
            `customerReview` = '$customerReviewSafe',
            `editorialReview` = '$editorialReviewSafe',
             `UPC` = '$UPCSafe' WHERE `id` = '$id'",
			    ""
		    );

	    } else {
		    $sql = $amazondb->prepare(
			    "INSERT INTO `wp_amazon_amazon_products` (
`keyType`, `keyValue`, `AIN`, 
`title`, `last_updated`, `detailPageURL`,
 `salesRank`, `author`, `brand`, 
 `department`, `color`, `ean`, 
 `feature`, `genre`, `isAdultProduct`, 
 `isAutographed`, `isMemorabilia`, `label`,
  `publisher`, `listPriceAmount`, `listPriceCurrencyCode`, 
  `listPriceFormattedPrice`, `manufacturer`, `productGroup`, 
  `productTypeName`, `lowestNewPriceAmount`, `lowestNewPriceCurrencyCode`, 
  `lowestNewPriceFormattedPrice`, `lowestUsedPriceAmount`, `lowestUsedPriceCurrencyCode`, `lowestUsedPriceFormattedPrice`, `lowestCollectiblePriceAmount`, `lowestCollectiblePriceCurrencyCode`, `lowestCollectiblePriceFormattedPrice`, `binding`,`avgRRF`, 
  `mpn`, `merchant`, `warranty`, 
  `amountSaved`, `availability`, `freeShippingMessage`, 
  `customerReview`, `editorialReview`, `UPC`) 
VALUES ('$keyTypeSafe', '$keyValueSafe', '$asinSafe', 
'$titleSafe', CURRENT_TIMESTAMP, '$detailPageUrlSafe', 
'$salesRankSafe', '$authorSafe', '$brandSafe', 
'$departmenSafe', '$colorSafe', '$eanSafe', 
'', '$genreSafe', '$isAdultProductSafe', 
'$isAutographedSafe', '$isMemorabiliaSafe', '$labelSafe', 
'$publisherSafe', '$listPriceAmountSafe', '$listPriceCurrencyCodeSafe', 
'$listPriceFormattedPriceSafe', '$manufacturerSafe', '$productGroupSafe', 
'$productTypeNameSafe', '$lowestNewPriceAmountSafe', '$lowestCollectiblePriceCurrencyCodeSafe', 
'$lowestNewPriceFormattedPriceSafe', '$lowestUsedPriceAmountSafe', '$lowestUsedPriceCurrencyCodeSafe', 
'$lowestUsedPriceFormattedPriceSafe', '$lowestCollectiblePriceAmountSafe', '$lowestCollectiblePriceCurrencyCodeSafe', 
'$lowestCollectiblePriceFormattedPriceSafe', '$typeSafe', '$avgRRFSafe', 
'$mpnSafe', '$merchantSafe', '$warrantySafe', 
'$amountSavedSafe', '$availabilitySafe', '$freeShippingMessageSafe', 
'$customerReviewSafe', '$editorialReviewSafe', '$UPCSafe')",
			    ""
		    );

	    }


	    $amazondb->query( $sql );

	    $id  = mysqli_insert_id( $conn );


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
	    foreach ( $product_links as $link ) {
		    $sql = $amazondb->prepare(
			    "INSERT INTO `wp_amazon_product_links` (`keyValue`, `link`) VALUES ('%s', '%s')
                  ON DUPLICATE KEY UPDATE `link` = '%s';",
			    $product->getKeyValue(),
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
	    foreach ( $languages as $language ) {
		    $sql = $amazondb->prepare(
			    "INSERT IGNORE INTO `wp_amazon_product_languages` (`keyValue`, `language`) VALUES ('%s', '%s');",
			    $product->getKeyValue(),
			    (string) is_object( $language ) ? $language->Name : "Unknown"
		    );
		    $amazondb->query( $sql );
		    if ( ! empty( $amazondb->last_error ) ) {
			    echo $amazondb->last_error;
			    throw new \Exception( $amazondb->last_error );
		    }
	    }

	    if ( ! empty( $related_products ) ) {
		    //$related_products = $product->related_products($relationshipType);
		    foreach ( $related_products as $related_product ) {
			    $sql = $amazondb->prepare(
				    "INSERT INTO `wp_amazon_product_related_products` (`keyValue`, `relatedProductAIN`, `relationshipType`) VALUES ('%s', '%s', '%s');",
				    $product->getKeyValue(),
				    $related_product->getAsin(),
				    $relationshipType
			    );
			    $amazondb->query( $sql );
			    if ( ! empty( $amazondb->last_error ) ) {
				    echo $amazondb->last_error;
				    throw new \Exception( $amazondb->last_error );
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
	    foreach ( $frequently_bought_together as $frequently_bought_together_product ) {
		    $sql = $amazondb->prepare(
			    "INSERT IGNORE INTO `wp_amazon_product_frequently_bought_together` (`keyValue`, `frequentlyBoughtTogether`) VALUES ('%s', '%s');",
			    $product->getKeyValue(),
			    (string) $frequently_bought_together_product->keyValue
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
	    foreach ( $images as $type => $image ) {
		    $sql = $amazondb->prepare(
			    "INSERT INTO `wp_amazon_product_images` (`type`, `keyValue`, `src`, `height`, `width`) VALUES ('%s', '%s', '%s', '%s', '%s')
 ON DUPLICATE KEY UPDATE `type` = '%s', `src`='%s', `height`='%s', `width`='%s';",
			    $type,
			    $product->getKeyValue(),
			    (string) isset( $image['src'] ) ? $image['src'] : $image['url'],
			    (int) $image['height'],
			    (int) $image['width'],
			    $type,
			    (string) isset( $image['src'] ) ? $image['src'] : $image['url'],
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
	    foreach ( $features as $feature ) {
		    $sql = $amazondb->prepare(
			    "INSERT INTO `wp_amazon_product_features` (`keyValue`, `feature`) VALUES ('%s', '%s')
 ON DUPLICATE KEY UPDATE `feature` = '%s';",
			    $product->getKeyValue(),
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

	    if ( empty( $dimensions ) ) {
		    $dimensions = array(
			    'Width'  => '',
			    'Height' => '',
			    'Length' => '',
			    'Weight' => ''
		    );
	    }


	    $sql = $amazondb->prepare(
		    "INSERT IGNORE INTO `wp_amazon_item_dimensions` (`keyValue`, `width`, `height`, `depth`, `weight`) VALUES ('%s', '%s', '%s', '%s', '%s');",
		    $product->getKeyValue(),
		    isset( $dimensions['Width'] ) ? $dimensions['Width'] : '',
		    isset( $dimensions['Height'] ) ? $dimensions['Height'] : '',
		    isset( $dimensions['Length'] ) ? $dimensions['Length'] : '',
		    isset( $dimensions['Weight'] ) ? $dimensions['Weight'] : ''
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

	    foreach ( $categories as $category ) {
		    $productIDSafe    = mysqli_real_escape_string( $conn, $id );
		    $seachIndexSafe   = mysqli_real_escape_string( $conn, $category->get_category_name() );
		    $searchPhraseSafe = mysqli_real_escape_string( $conn, $category->get_category_name() );
		    $RRF              = $product->getSalesRank() / $category->get_number_of_items();
		    $RRFSafe          = mysqli_real_escape_string( $conn, $RRF );
		    $sql              = "INSERT IGNORE INTO `wp_amazon_product_categories` 
              (`productID`, `searchPhrase`, `searchIndex`, `RRF`)
               VALUES ('$productIDSafe', '$searchPhraseSafe', '$seachIndexSafe', '$RRFSafe' );";
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

	    if ( ! empty( $image_sets ) ) {
		    foreach ( $image_sets as $image_set ) {

			    if ( ! isset( $image_set['swatch'] ) ) {
				    $image_set['swatch'] = array(
					    'height' => '',
					    'width'  => '',
					    'url'    => ''
				    );
			    }

			    if ( ! isset( $image_set['hires'] ) ) {
				    $image_set['hires'] = array(
					    'height' => '',
					    'width'  => '',
					    'url'    => ''
				    );
			    }
			    $sql = $amazondb->prepare(
				    "INSERT INTO `wp_amazon_product_image_sets` (`keyValue`, `type`, `height`, `width`, `url`, `last_updated`) VALUES ('%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `height`='%s', `width`='%s', `url` = '%s', `last_updated` = '%s';",
				    $product->getKeyValue(),
				    'swatch',
				    $image_set['swatch']['height'],
				    $image_set['swatch']['width'],
				    $image_set['swatch']['url'],
				    $image_set['hires']['height'],
				    $image_set['hires']['width'],
				    $image_set['hires']['url'],
				    date( "Y-m-d H:i:s" )
			    );
			    $amazondb->query( $sql );

			    if ( ! isset( $image_set['small'] ) ) {
				    $image_set['small'] = array(
					    'height' => '',
					    'width'  => '',
					    'url'    => ''
				    );
			    }

			    $sql = $amazondb->prepare(
				    "INSERT INTO `wp_amazon_product_image_sets` (`keyValue`, `type`, `height`, `width`, `url`, `last_updated`) VALUES ('%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `height`='%s', `width`='%s', `url` = '%s', `last_updated` = '%s';",
				    $product->getKeyValue(),
				    'small',
				    $image_set['small']['height'],
				    $image_set['small']['width'],
				    $image_set['small']['url'],
				    $image_set['hires']['height'],
				    $image_set['hires']['width'],
				    $image_set['hires']['url'],
				    date( "Y-m-d H:i:s" )
			    );
			    $amazondb->query( $sql );

			    if ( ! isset( $image_set['thumb'] ) ) {
				    $image_set['thumb'] = array(
					    'height' => '',
					    'width'  => '',
					    'url'    => ''
				    );
			    }

			    $sql = $amazondb->prepare(
				    "INSERT INTO `wp_amazon_product_image_sets` (`keyValue`, `type`, `height`, `width`, `url`, `last_updated`) VALUES ('%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `height`='%s', `width`='%s', `url` = '%s', `last_updated` = '%s';",
				    $product->getKeyValue(),
				    'thumb',
				    $image_set['thumb']['height'],
				    $image_set['thumb']['width'],
				    $image_set['thumb']['url'],
				    $image_set['hires']['height'],
				    $image_set['hires']['width'],
				    $image_set['hires']['url'],
				    date( "Y-m-d H:i:s" )
			    );
			    $amazondb->query( $sql );

			    if ( ! isset( $image_set['medium'] ) ) {
				    $image_set['medium'] = array(
					    'height' => '',
					    'width'  => '',
					    'url'    => ''
				    );
			    }

			    $sql = $amazondb->prepare(
				    "INSERT INTO `wp_amazon_product_image_sets` (`keyValue`, `type`, `height`, `width`, `url`, `last_updated`) VALUES ('%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `height`='%s', `width`='%s', `url` = '%s', `last_updated` = '%s';",
				    $product->getKeyValue(),
				    'medium',
				    $image_set['medium']['height'],
				    $image_set['medium']['width'],
				    $image_set['medium']['url'],
				    $image_set['hires']['height'],
				    $image_set['hires']['width'],
				    $image_set['hires']['url'],
				    date( "Y-m-d H:i:s" )
			    );
			    $amazondb->query( $sql );

			    if ( ! isset( $image_set['tiny'] ) ) {
				    $image_set['tiny'] = array(
					    'height' => '',
					    'width'  => '',
					    'url'    => ''
				    );
			    }

			    $sql = $amazondb->prepare(
				    "INSERT INTO `wp_amazon_product_image_sets` (`keyValue`, `type`, `height`, `width`, `url`, `last_updated`) VALUES ('%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `height`='%s', `width`='%s', `url` = '%s', `last_updated` = '%s';",
				    $product->getKeyValue(),
				    'tiny',
				    $image_set['tiny']['height'],
				    $image_set['tiny']['width'],
				    $image_set['tiny']['url'],
				    $image_set['hires']['height'],
				    $image_set['hires']['width'],
				    $image_set['hires']['url'],
				    date( "Y-m-d H:i:s" )
			    );
			    $amazondb->query( $sql );

			    if ( ! isset( $image_set['large'] ) ) {
				    $image_set['large'] = array(
					    'height' => '',
					    'width'  => '',
					    'url'    => ''
				    );
			    }

			    $sql = $amazondb->prepare(
				    "INSERT INTO `wp_amazon_product_image_sets` (`keyValue`, `type`, `height`, `width`, `url`, `last_updated`) VALUES ('%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `height`='%s', `width`='%s', `url` = '%s', `last_updated` = '%s';",
				    $product->getKeyValue(),
				    'large',
				    $image_set['large']['height'],
				    $image_set['large']['width'],
				    $image_set['large']['url'],
				    $image_set['hires']['height'],
				    $image_set['hires']['width'],
				    $image_set['hires']['url'],
				    date( "Y-m-d H:i:s" )
			    );
			    $amazondb->query( $sql );

			    $sql = $amazondb->prepare(
				    "INSERT INTO `wp_amazon_product_image_sets` (`keyValue`, `type`, `height`, `width`, `url`, `last_updated`) VALUES ('%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `height`='%s', `width`='%s', `url` = '%s', `last_updated` = '%s';",
				    $product->getKeyValue(),
				    'hires',
				    $image_set['hires']['height'],
				    $image_set['hires']['width'],
				    $image_set['hires']['url'],
				    $image_set['hires']['height'],
				    $image_set['hires']['width'],
				    $image_set['hires']['url'],
				    date( "Y-m-d H:i:s" )
			    );
			    $amazondb->query( $sql );

			    if ( ! empty( $amazondb->last_error ) ) {
				    echo $amazondb->last_error;
				    throw new \Exception( $amazondb->last_error );
			    }

		    } // for each image set
		    // var_dump('jjjj');
		    //die();


		    $pd = AmazonCache::getProduct( $product->getKeyValue() );
		    //var_dump( $pd );
		    //die();
	    }

	    if ( ! empty( $id ) ) {
		    $product->id = $id;
	    }


	    return $product;
    }

    public static function getKeywords( $phrase, $rrf, $depth) {

	    global $conn;

	    $transient = new Transient($conn);
	    $data = $transient->fetch('keywords' . $phrase . $rrf);

        if ($data == false) {

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
	        $transient->save('keywords' . $phrase . $rrf, $data, 3600 * 24 * 1);

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