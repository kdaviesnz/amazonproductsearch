<?php
declare(strict_types=1);


namespace kdaviesnz\amazon;


use PHPUnit\Runner\Exception;

class AmazonParser implements IAmazonParser
{

    public $moreSearchResultsUrl; // string
    public $totalResults = 0; //int
    public $totalPages; //int
    public $items = array();
    public $numberResults = 0;

    public function generate_products($items)
    {

        foreach ($items as $item) {

            try {
                $product = AmazonCache::getProduct( (string) $item->ASIN, '');
                throw new \Exception('testing'); // @todo remove
            } catch (\Exception $e) {

                $item_links_raw = (array) $item->ItemLinks->children();
                $item_links_raw = $item_links_raw['ItemLink'];
                $item_links = array();
                foreach ($item_links_raw as $v => $item_link) {
                    $item_links[ (string) $item_link->Description ] = (string) $item_link->URL;
                }

                $item_attributes = (array) $item->ItemAttributes;
                $offers = (array) $item->Offers;

                $mpn = isset($item_attributes['MPN']) ? $item_attributes['MPN'] : '';

                $merchant = '';
                if ($item->Offers) {
                    $temp = (array)$item->Offers->children();
                    $merchant = isset($temp['Offer']) ? (string)$temp['Offer']->Merchant->Name : '';
                }


                $warranty = isset($item_attributes['Warranty']) ? $item_attributes['Warranty'] : '';


                $temp = isset($item->ImageSets) ? (array)$item->ImageSets:array();
                $image_sets = array();

                foreach ($temp as $image_set) {

                	if (is_object($image_set)) {
		                $image_sets[] = array(
			                'swatch' => array(
				                'url'    => ! is_object( $image_set->SwatchImage ) ? "" : (string) $image_set->SwatchImage->URL,
				                'height' => (string) $image_set->SwatchImage->Height,
				                'width'  => (string) $image_set->SwatchImage->Width,
			                ),
			                'small'  => array(
				                'url'    => (string) $image_set->SmallImage->URL,
				                'height' => (string) $image_set->SmallImage->Height,
				                'width'  => (string) $image_set->SmallImage->Width,
			                ),
			                'thumb'  => array(
				                'url'    => (string) $image_set->ThumbnailImage->URL,
				                'height' => (string) $image_set->ThumbnailImage->Height,
				                'width'  => (string) $image_set->ThumbnailImage->Width,
			                ),
			                'tiny'   => array(
				                'url'    => (string) $image_set->TinyImage->URL,
				                'height' => (string) $image_set->TinyImage->Height,
				                'width'  => (string) $image_set->TinyImage->Width,
			                ),
			                'medium' => array(
				                'url'    => (string) $image_set->MediumImage->URL,
				                'height' => (string) $image_set->MediumImage->Height,
				                'width'  => (string) $image_set->MediumImage->Width,
			                ),
			                'large'  => array(
				                'url'    => (string) $image_set->LargeImage->URL,
				                'height' => (string) $image_set->LargeImage->Height,
				                'width'  => (string) $image_set->LargeImage->Width,
			                ),
			                'hires'  => array(
				                'url'    => (string) $image_set->HiResImage->URL,
				                'height' => (string) $image_set->HiResImage->Height,
				                'width'  => (string) $image_set->HiResImage->Width,
			                ),
		                );
	                }
                }

                $images = array();
                $images['small'] = array(
                    'url' => $item->SmallImage->URL,
                    'height' => $item->SmallImage->Height,
                    'width' => $item->SmallImage->Width,
                );
                $images['medium'] = array(
                    'url' => $item->MediumImage->URL,
                    'height' => $item->MediumImage->Height,
                    'width' => $item->MediumImage->Width,
                );
                $images['large'] = array(
                    'url' => $item->LargeImage->URL,
                    'height' => $item->LargeImage->Height,
                    'width' => $item->LargeImage->Width,
                );

                $related_products = array();

                $similar_products = array();
                if ($item->SimilarProducts) {
                    foreach ($item->SimilarProducts->children() as $similar_item) {
                        if ('SimilarProduct' === $similar_item->getName()) {

                            $similar_products[] = $similar_item;
                        }
                    }
                }

                $categories = array();


                if ($item->BrowseNodes) {


                    foreach ($item->BrowseNodes->children() as $node) {

                        // One category per item. Rest are ancestor categories.

                        if ('BrowseNode' === $node->getName()) {

                            $ancestor_categories = array();

                            if (!empty($node->Ancestors)) {
                                // This caches the ancestor category and also does a category search (AmazonProductSearch::categorySearch)) to get the number of items in the category.
                                // Returns array of AmazonCategory objects.
                                //   $ancestor_categories = $this->get_categories( $categories, (array) $node->Ancestors, array(), 1, 3);
                            }

                            //  var_dump( (string) $node->BrowseNodeId );

                            try {
                                $category_search_results = AmazonProductSearch::categorySearch((string)$node->BrowseNodeId, array('Small'));
                            } catch (\Exception $e) {
                                $category_search_results = null;
                                //  break; // exit out of the loop.
                            }

                            if (!empty($category_search_results)) {
                                $amazonCategory = new AmazonCategory(
                                    (string)$node->BrowseNodeId,
                                    (string)$node->Name,
                                    $ancestor_categories,
                                    $category_search_results['total_results']
                                );
                                AmazonCache::cacheCategory($amazonCategory);
                                $categories[] = $amazonCategory;
                            }


                        }
                    }
                }

                $rrfs = $this->get_rrfs($item, array(), $categories);

                // $rrfs = array();

              //  var_dump($similar_products);
                $product = new \kdaviesnz\amazon\AmazonProduct(
                    (string)$item->ASIN, //1
                    (string)$item->DetailPageURL,
                    (array)$item_links,
                    (int)$item->SalesRank,
                    (string)isset($item_attributes['Author'][1]) ? (string)$item_attributes['Author'][1] : '', // 5
                    (string)isset($item_attributes['Binding']) ? $item_attributes['Binding'] : '',
                    (string)isset($item_attributes['Brand']) ? $item_attributes['Brand'] : '',
                    (string)isset($item_attributes['Department']) ? $item_attributes['Department'] : '',
                    (string)isset($item_attributes['Color']) ? $item_attributes['Color'] : '',
                    (string)isset($item_attributes['EAN']) ? $item_attributes['EAN'] : '',//10
                    isset($item_attributes['Feature']) ? (array)$item_attributes['Feature']:array(),
                    (string)isset($item_attributes['Genre']) ? $item_attributes['Genre'] : '',
                    (bool)isset($item_attributes['IsAdultProduct']) && $item_attributes['IsAdultProduct'] == 1,
                    (bool)isset($item_attributes['IsAutographed']) && $item_attributes['IsAutographed'] == 1,
                    (bool)isset($item_attributes['IsMemorabilia']) && $item_attributes['IsMemorabilia'] == 1,
                    isset($item_attributes['ItemDimensions']) ? (array)$item_attributes['ItemDimensions']:array(),
                    (string)isset($item_attributes['Label']) ? $item_attributes['Label'] : '',
                    isset($item_attributes['Languages']) ? (array)$item_attributes['Languages']:array(),
                    isset($item_attributes['ListPrice']) ? (string)$item_attributes['ListPrice']->Amount : '',
                    isset($item_attributes['ListPrice']) ? (string)$item_attributes['ListPrice']->CurrencyCode : '',// 20
                    isset($item_attributes['ListPrice']) ? (string)$item_attributes['ListPrice']->FormattedPrice : '',
                    (string)isset($item_attributes['Manufacturer']) ? $item_attributes['Manufacturer'] : '',
                    (string)$item_attributes['ProductGroup'],
                    (string)$item_attributes['ProductTypeName'],
                    (string)isset($item_attributes['Publisher']) ? $item_attributes['Publisher'] : '',
                    (string)$item_attributes['Title'],
                    isset($item_attributes['OfferSummary']) ? (string)$item_attributes['OfferSummary']->LowestNewPrice->Amount : '',
                    isset($item_attributes['OfferSummary']) ? (string)$item_attributes['OfferSummary']->LowestNewPrice->CurrencyCode : '',
                    isset($item_attributes['OfferSummary']) ? (string)$item_attributes['OfferSummary']->LowestNewPrice->FormattedPrice : '',
                    isset($item_attributes['OfferSummary']) ? (string)$item_attributes['OfferSummary']->LowestUsedPrice->Amount : '', // 30
                    isset($item_attributes['OfferSummary']) ? (string)$item_attributes['OfferSummary']->LowestUsedPrice->CurrencyCode : '',
                    isset($item_attributes['OfferSummary']) ? (string)$item_attributes['OfferSummary']->LowestUsedPrice->FormattedPrice : '',
                    isset($item_attributes['OfferSummary']) ? (string)$item_attributes['OfferSummary']->LowestCollectiblePrice->Amount : '',
                    isset($item_attributes['OfferSummary']) ? (string)$item_attributes['OfferSummary']->LowestCollectiblePrice->CurrencyCode : '',
                    isset($item_attributes['OfferSummary']) ? (string)$item_attributes['OfferSummary']->LowestCollectiblePrice->FormattedPrice : '',
                    $images,
                    $similar_products,
                    $categories,
                    $rrfs, // 39
                    $mpn,
                    $merchant,
                    $warranty,
                    $image_sets,
                    !isset($offers['Offer']) ?null: (float) $offers['Offer']->OfferListing->AmountSaved->FormattedPrice,
	                !isset($offers['Offer']) ?null:(string) $offers['Offer']->OfferListing->Availability,
	                !isset($offers['Offer']) ?null:(string) '0' == $offers['Offer']->OfferListing->IsEligibleForSuperSaverShipping ? 'Not available for free shipping' : 'Available for free shipping',
                    (string) $item_links['All Customer Reviews'],
                    is_object($item->EditorialReviews) && is_object($item->EditorialReviews->EditorialReview) ? (string) $item->EditorialReviews->EditorialReview->Content:""
                );

                //var_dump('3');
                //die();

                AmazonCache::cacheProduct($product, '', $related_products);
                try {
                    // This only gets the product if it has an average RFF greater than 0.
                    $product = AmazonCache::getProduct($product->getAsin());
                } catch (\Exception $e) {
                    $product = null;
                }

            } // catch

           // var_dump($product);
           // die();
            yield $product;

        } // foreach
    }

    /**
     * Get items in a category.
     *
     * @param string $category_search_results_xml
     * @return array
     * @throws \Exception
     */
    public function parse_category_search_results($category_search_results_xml ) {

        $pxml = simplexml_load_string( $category_search_results_xml );

        if ($pxml && $pxml->Items->children()) {
            foreach ($pxml->Items->children() as $item) {
                if ('Item' === $item->getName()) {

                    $this->items[] = (array) $item;
                }
            }
        }

        $this->totalResults = (int) $pxml->Items->TotalResults;

        return array(
            'items' => $this->items,
        );
    }

    public function parse_cart( $cart_xml )  {

        $pxml = simplexml_load_string( $cart_xml );
        if ( $pxml->Cart->Request->IsValid == 'False' ) {
            throw new \Exception( (string) $pxml->Cart->Request->Errors->Error->Message );
        }

        $cartItems = array();
        $cartItemsXML = (array) $pxml->Cart->CartItems;

        foreach( $cartItemsXML as $cartItemXML ) {

            if ( property_exists( $cartItemXML, 'CartItemId' ) && ! empty( (string)$cartItemXML->CartItemId ) ) {
                $cartItems[] = new AmazonCartItem
                (
                    (string)$cartItemXML->CartItemId,
                    (string)$cartItemXML->ASIN,
                    (string)$cartItemXML->SellerNickname,
                    (int)$cartItemXML->Quantity,
                    (string)$cartItemXML->Title,
                    (int)$cartItemXML->ItemTotal->Amount,
                    (string)$cartItemXML->ItemTotal->CurrencyCode,
                    (float)$cartItemXML->ItemTotal->FormattedPrice
                );
            }
        }

        $cart = new AmazonCart
        (
            (string) $pxml->Cart->CartId,
            (string) $pxml->Cart->PurchaseURL,
            (int) $pxml->Cart->SubTotal->Amount,
            (string) $pxml->Cart->SubTotal->CurrencyCode,
            (float) $pxml->Cart->SubTotal->FormattedPrice,
            (array) $cartItems,
            (string) $pxml->Cart->HMAC
        );

        return $cart;

    }

    public function parse_item_search_results( $search_results_xml ) {

        $pxml = simplexml_load_string( $search_results_xml );

        if ($pxml && $pxml->Items->children()) {
            foreach ($pxml->Items->children() as $item) {
                if ('Item' === $item->getName()) {
                    $this->items[] = $item;
                }
            }
            $this->moreSearchResultsUrl = (string) $pxml->Items->MoreSearchResultsUrl;
            $this->totalResults = (int) $pxml->Items->TotalResults;
            $this->totalPages = (int) $pxml->Items->TotalPages;
            $this->numberResults = count($this->items);
        }


        $products = array();

     //   header( 'Content-Type:application/xml' );
      //  echo $search_results_xml;
      //  die();

        foreach( $this->items as $item ) {


            try {
                $product = AmazonCache::getProduct( (string) $item->ASIN, '' );
            } catch( \Exception $e ) {

                $item_links_raw = (array) $item->ItemLinks->children();
                $item_links_raw  = $item_links_raw['ItemLink'];
                $item_links = array();
                foreach( $item_links_raw as $v=>$item_link ) {
                    $item_links[(string) $item_link->Description] = (string) $item_link->URL;
                }


                $item_attributes = (array) $item->ItemAttributes;
                $offers = (array) $item->Offers;

                $mpn = isset( $item_attributes['MPN'] ) ? $item_attributes['MPN']: '';

                $merchant = '';
                if($item->Offers) {
                    $temp = (array)$item->Offers->children();
                    $merchant = isset($temp['Offer']) ? (string)$temp['Offer']->Merchant->Name : '';
                }

                $images = array();
                $images['small'] = array(
                    'url' => $item->SmallImage->URL,
                    'height' => $item->SmallImage->Height,
                    'width' => $item->SmallImage->Width,
                );
                $images['medium'] = array(
                    'url' => $item->MediumImage->URL,
                    'height' => $item->MediumImage->Height,
                    'width' => $item->MediumImage->Width,
                );
                $images['large'] = array(
                    'url' => $item->LargeImage->URL,
                    'height' => $item->LargeImage->Height,
                    'width' => $item->LargeImage->Width,
                );

                $related_products = array();

                $similar_products = array();
                if ( $item->SimilarProducts ) {
                    foreach ($item->SimilarProducts->children() as $similar_item) {
                        if ('SimilarProduct' === $similar_item->getName()) {
                            $similar_products[] = $similar_item;
                        }
                    }
                }


                $categories = array();

                //    header( 'Content-Type:application/xml' );
                  // echo $search_results_xml;
                  // die();

                if ( $item->BrowseNodes ) {
                    foreach ( $item->BrowseNodes->children() as $node ) {

                        if ('BrowseNode' === $node->getName()) {

                            $ancestor_categories = array();

                            if (!empty($node->Ancestors)) {
                                // This caches the ancestor category and also does a category search (AmazonProductSearch::categorySearch)) to get the number of items in the category.
                                // Returns array of AmazonCategory objects.
                                $ancestor_categories = $this->get_categories( $categories, (array) $node->Ancestors, array(), 1, 3);
                            }

                            //  var_dump( (string) $node->BrowseNodeId );


                            $category_search_results = AmazonProductSearch::categorySearch((string)$node->BrowseNodeId, array('Small'));
                            $amazonCategory = new AmazonCategory(
                                (string)$node->BrowseNodeId,
                                (string)$node->Name,
                                $ancestor_categories,
                                $category_search_results['total_results']
                            );
                            AmazonCache::cacheCategory( $amazonCategory );
                            $categories[] = $amazonCategory;

                        }
                    }
                }

                //   die();

                $rrfs = $this->get_rrfs( $item, array(), $categories );

                if (!is_object($item)) {
                	throw new Exception("Item is not an object");
                }

	            if (!is_object($item->EditorialReviews)) {
		            throw new Exception("Item does not have any editorial reviews");
	            }

                if ($item->EditorialReviews->EditorialReview == null) {
	                $editorialReview = "";
                } else {
	                $editorialReview = (string) $item->EditorialReviews->EditorialReview->Content;
                }

                $customerReviewsLink = isset( $item_links['All Customer Reviews']) ?  $item_links['All Customer Reviews'] : '';
                $warranty = isset($item_attributes['Warranty']) ? $item_attributes['Warranty'] : '';
                $temp = isset($item->ImageSets) ? (array)$item->ImageSets:array();
                $image_sets = array();
                foreach ($temp as $image_set) {
                    $image_sets[] = array(
                        'swatch' => array(
                            'url' => (string)$image_set->SwatchImage->URL,
                            'height' => (string)$image_set->SwatchImage->Height,
                            'width' => (string)$image_set->SwatchImage->Width,
                        ),
                        'small' => array(
                            'url' => (string)$image_set->SmallImage->URL,
                            'height' => (string)$image_set->SmallImage->Height,
                            'width' => (string)$image_set->SmallImage->Width,
                        ),
                        'thumb' => array(
                            'url' => (string)$image_set->ThumbnailImage->URL,
                            'height' => (string)$image_set->ThumbnailImage->Height,
                            'width' => (string)$image_set->ThumbnailImage->Width,
                        ),
                        'tiny' => array(
                            'url' => (string)$image_set->TinyImage->URL,
                            'height' => (string)$image_set->TinyImage->Height,
                            'width' => (string)$image_set->TinyImage->Width,
                        ),
                        'medium' => array(
                            'url' => (string)$image_set->MediumImage->URL,
                            'height' => (string)$image_set->MediumImage->Height,
                            'width' => (string)$image_set->MediumImage->Width,
                        ),
                        'large' => array(
                            'url' => (string)$image_set->LargeImage->URL,
                            'height' => (string)$image_set->LargeImage->Height,
                            'width' => (string)$image_set->LargeImage->Width,
                        ),
                        'hires' => array(
                            'url' => (string)$image_set->HiResImage->URL,
                            'height' => (string)$image_set->HiResImage->Height,
                            'width' => (string)$image_set->HiResImage->Width,
                        ),
                    );
                }

                $availability = (string) $offers['Offer']->OfferListing->Availability;

                $product = new \kdaviesnz\amazon\AmazonProduct(
                    (string)$item->ASIN, //1
                    (string)$item->DetailPageURL,
                    (array)$item_links,
                    (int)$item->SalesRank,
                    (string)isset($item_attributes['Author'][1])?(string)$item_attributes['Author'][1]:'', // 5
                    (string)isset($item_attributes['Binding'])?$item_attributes['Binding']:'',
                    (string)isset($item_attributes['Brand'])?$item_attributes['Brand']:'',
                    (string)isset($item_attributes['Department'])?$item_attributes['Department']:'',
                    (string)isset($item_attributes['Color'])?$item_attributes['Color']:'',
                    (string)isset($item_attributes['EAN'])?$item_attributes['EAN']:'',//10
                    isset($item_attributes['Feature'])?(array)$item_attributes['Feature']:array(),
                    (string)isset($item_attributes['Genre'])?$item_attributes['Genre']:'',
                    (bool)isset($item_attributes['IsAdultProduct']) && $item_attributes['IsAdultProduct'] == 1,
                    (bool)isset($item_attributes['IsAutographed']) && $item_attributes['IsAutographed'] == 1,
                    (bool)isset($item_attributes['IsMemorabilia']) && $item_attributes['IsMemorabilia'] == 1,
                    isset($item_attributes['ItemDimensions'])?(array)$item_attributes['ItemDimensions']:array(),
                    (string)isset($item_attributes['Label'])?$item_attributes['Label']:'',
                    isset($item_attributes['Languages'])?(array)$item_attributes['Languages']:array(),
                    isset($item_attributes['ListPrice'])?(string)$item_attributes['ListPrice']->Amount:'',
                    isset($item_attributes['ListPrice'])?(string)$item_attributes['ListPrice']->CurrencyCode:'',// 20
                    isset($item_attributes['ListPrice'])?(string)$item_attributes['ListPrice']->FormattedPrice:'',
                    (string)isset($item_attributes['Manufacturer'])?$item_attributes['Manufacturer']:'',
                    (string)$item_attributes['ProductGroup'],
                    (string)$item_attributes['ProductTypeName'],
                    (string)isset($item_attributes['Publisher'])?$item_attributes['Publisher']:'',
                    (string)$item_attributes['Title'],
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestNewPrice->Amount: '',
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestNewPrice->CurrencyCode:'',
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestNewPrice->FormattedPrice:'',
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestUsedPrice->Amount:'', // 30
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestUsedPrice->CurrencyCode:'',
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestUsedPrice->FormattedPrice:'',
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestCollectiblePrice->Amount:'',
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestCollectiblePrice->CurrencyCode:'',
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestCollectiblePrice->FormattedPrice:'',
                    $images,
                    $similar_products,
                    $categories,
                    $rrfs, // 39
                    $mpn,
                    $merchant,
                    $warranty,
                    $image_sets,
                    (float) $offers['Offer']->OfferListing->AmountSaved->Amount,
                    $availability, // 45
                    '0' == (string) $offers['Offer']->OfferListing->IsEligibleForSuperSaverShipping ? 'Not available for free shipping' : 'Available for free shipping',
                    $customerReviewsLink,
                    !empty( $editorialReview ) ? $editorialReview: 'blank'
                );


                AmazonCache::cacheProduct( $product, '', $related_products );
            }

            $products[] = $product;

        }

        return array(
            'items' => $products,
        );
    }

    public function parse_search_results( $search_results_xml ) {


        $pxml = simplexml_load_string( $search_results_xml );

        if ($pxml && $pxml->Items->children()) {
            foreach ($pxml->Items->children() as $item) {
                if ('Item' === $item->getName()) {
                    $this->items[] = $item;
                }
            }
            $this->moreSearchResultsUrl = (string) $pxml->Items->MoreSearchResultsUrl;
            $this->totalResults = (int) $pxml->Items->TotalResults;
            $this->totalPages = (int) $pxml->Items->TotalPages;
            $this->numberResults = count($this->items);
        }


        $products = array();

        //  header( 'Content-Type:application/xml' );
   //      echo $search_results_xml;
   //      die();

        //foreach( $this->items as $item ) {
        foreach( AmazonParser::generate_products( $this->items) as $product ) {


            if (!empty($product)) {
                $products[] = $product;
            }



        }// for each item

        return array(
            'items' => $products,
        );
    }



    private function get_rrfs( $item, $rrfs, $categories ) {

        foreach ( $categories as $category ) {
            $rrf = (int) $item->SalesRank / $category->get_number_of_items();
            $rrfs[ $category->get_category_id() ] = $rrf;
            $ancestor_categories = $category->get_ancestor_categories();
            if ( ! empty( $ancestor_categories ) ) {
                $rrfs = $this->get_rrfs( $item, $rrfs, $ancestor_categories );
            }
        }
        return $rrfs;
    }

    private function get_categories( $categories, $children, $ancestors, $current_depth, $max_depth ) {

        $ancestor_categories = array();

        foreach( $children as $node ) {


            if ( ! empty( $node->Ancestors ) && $current_depth < $max_depth ) {
                //  $ancestor_categories = $this->get_categories( $categories, (array) $node->Ancestors, $ancestors, $current_depth + 1, $max_depth );
            }

            $items_in_category = AmazonProductSearch::categorySearch((string)$node->BrowseNodeId, array('Small'));

            if ( 'BrowseNode' === $node->getName()) {
                $amazonCategory = new AmazonCategory( (string) $node->BrowseNodeId, (string) $node->Name, $ancestor_categories, $items_in_category['total_results'] );
                $categories[] = $amazonCategory;
                AmazonCache::cacheCategory( $amazonCategory );
            }
        }

        return $categories;
    }

    public function parse_related_items_search_results( $search_results_xml ) {

        $pxml = simplexml_load_string( $search_results_xml );

        // header( 'Content-Type:application/xml' );
        // echo $search_results_xml;
        // die();
        $this->items[] = array();

        if ($pxml && $pxml->Items->Item->RelatedItems && $pxml->Items->Item->RelatedItems->children()) {
            foreach ($pxml->Items->Item->RelatedItems->children() as $item) {
                if ('RelatedItem' === $item->getName()) {
                    $this->items[] = $item->Item;
                }
            }
        }

        $this->moreSearchResultsUrl = (string) $pxml->Items->MoreSearchResultsUrl;
        $this->totalResults = (int) $pxml->Items->TotalResults;
        $this->totalPages = (int) $pxml->Items->TotalPages;
        $this->numberResults = count($this->items);

        $products = array();

        foreach( $this->items as $item ) {

            if ( is_object( $item ) ) {

                $item_links_raw = (array) $item->ItemLinks->children();
                $item_links_raw  = $item_links_raw['ItemLink'];
                $item_links = array();
                foreach( $item_links_raw as $v=>$item_link ) {
                    $item_links[] = (string) $item_link->URL;
                }

                $item_attributes = (array)$item->ItemAttributes;
                $offers = (array) $item->Offers;

                $mpn = isset( $item_attributes['MPN'] ) ? $item_attributes['MPN']: '';

                $merchant = '';
                if($item->Offers) {
                    $temp = (array)$item->Offers->children();
                    $merchant = isset($temp['Offer']) ? (string)$temp['Offer']->Merchant->Name : '';
                }

                $images = array();
                $images['small'] = array(
                    'url' => $item->SmallImage->URL,
                    'height' => $item->SmallImage->Height,
                    'width' => $item->SmallImage->Width,
                );
                $images['medium'] = array(
                    'url' => $item->MediumImage->URL,
                    'height' => $item->MediumImage->Height,
                    'width' => $item->MediumImage->Width,
                );
                $images['large'] = array(
                    'url' => $item->LargeImage->URL,
                    'height' => $item->LargeImage->Height,
                    'width' => $item->LargeImage->Width,
                );

                $related_products = array();

                $similar_products = array();
                if ($item->SimilarProducts) {
                    foreach ($item->SimilarProducts->children() as $similar_item) {
                        if ('SimilarProduct' === $similar_item->getName()) {
                            $similar_products[] = $similar_item;
                        }
                    }
                }

                $categories = array();
                if ($item->BrowseNodes) {
                    foreach ($item->BrowseNodes->children() as $node) {
                        if ('BrowseNode' === $node->getName()) {
                            if (!empty($node->Ancestors->children)) {
                                $ancestor_categories = $this->get_categories($categories, (array)$node->Ancestors, array());
                            }
                            $categories[] = new AmazonCategory((string)$node->BrowseNodeId, (string)$node->Name, $ancestor_categories);
                        }
                    }
                }

                // RRF
                /*
                 *         /*
             * Rank - the 5% secret
        24,000,000 products in x
        Product is ranked #28
        28 / 24,000,000 = %5 or under !!! high seller
     Use () in product search to get all products
             */
                $rrfs = $this->get_rrfs( $item, array(), $categories);

                $product = new \kdaviesnz\amazon\AmazonProduct(
                    (string)$item->ASIN, //1
                    (string)$item->DetailPageURL,
                    (array)$item_links,
                    (int)$item->SalesRank,
                    (string)isset($item_attributes['Author'][1])?(string)$item_attributes['Author'][1]:'', // 5
                    (string)isset($item_attributes['Binding'])?$item_attributes['Binding']:'',
                    (string)isset($item_attributes['Brand'])?$item_attributes['Brand']:'',
                    (string)isset($item_attributes['Department'])?$item_attributes['Department']:'',
                    (string)isset($item_attributes['Color'])?$item_attributes['Color']:'',
                    (string)isset($item_attributes['EAN'])?$item_attributes['EAN']:'',//10
                    isset($item_attributes['Feature'])?(array)$item_attributes['Feature']:array(),
                    (string)isset($item_attributes['Genre'])?$item_attributes['Genre']:'',
                    (bool)isset($item_attributes['IsAdultProduct']) && $item_attributes['IsAdultProduct'] == 1,
                    (bool)isset($item_attributes['IsAutographed']) && $item_attributes['IsAutographed'] == 1,
                    (bool)isset($item_attributes['IsMemorabilia']) && $item_attributes['IsMemorabilia'] == 1,
                    isset($item_attributes['ItemDimensions'])?(array)$item_attributes['ItemDimensions']:array(),
                    (string)isset($item_attributes['Label'])?$item_attributes['Label']:'',
                    isset($item_attributes['Languages'])?(array)$item_attributes['Languages']:array(),
                    isset($item_attributes['ListPrice'])?(string)$item_attributes['ListPrice']->Amount:'',
                    isset($item_attributes['ListPrice'])?(string)$item_attributes['ListPrice']->CurrencyCode:'',// 20
                    isset($item_attributes['ListPrice'])?(string)$item_attributes['ListPrice']->FormattedPrice:'',
                    (string)isset($item_attributes['Manufacturer'])?$item_attributes['Manufacturer']:'',
                    (string)$item_attributes['ProductGroup'],
                    (string)$item_attributes['ProductTypeName'],
                    (string)isset($item_attributes['Publisher'])?$item_attributes['Publisher']:'',
                    (string)$item_attributes['Title'],
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestNewPrice->Amount: '',
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestNewPrice->CurrencyCode:'',
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestNewPrice->FormattedPrice:'',
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestUsedPrice->Amount:'', // 30
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestUsedPrice->CurrencyCode:'',
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestUsedPrice->FormattedPrice:'',
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestCollectiblePrice->Amount:'',
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestCollectiblePrice->CurrencyCode:'',
                    isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestCollectiblePrice->FormattedPrice:'',
                    $images,
                    $similar_products,
                    $categories,
                    $rrfs, // 39
                    $mpn,
                    $merchant,
                    $warranty,
                    $image_sets,
                    (float) $offers['Offer']->OfferListing->AmountSaved->Amount,
                    (string) $offers['Offer']->OfferListing->Availability,
                    (string) '0' == $offers['Offer']->OfferListing->IsEligibleForSuperSaverShipping ? 'Not available for free shipping' : 'Available for free shipping',
                    (string) $item_links['All Customer Reviews'],
                    (string) $item->EditorialReviews->EditorialReview->Content
                );

                //$product->add_to_database();
                AmazonCache::cacheProduct($product, '', $related_products);
                $products[] = $product;
            }

        }

        return array(
            'items' => $products,
        );
    }

    public function parse_frequently_bought_together_search_results( $search_results_xml ) {

        $pxml = simplexml_load_string( $search_results_xml );

        if ($pxml && $pxml->Items->Item->SimilarProducts->children()) {
            foreach ($pxml->Items->Item->SimilarProducts->children() as $item) {
                if ('SimilarProduct' === $item->getName()) {
                    $this->items[] = $item;
                }
            }
            $this->moreSearchResultsUrl = (string) $pxml->Items->MoreSearchResultsUrl;
            $this->totalResults = (int) $pxml->Items->TotalResults;
            $this->totalPages = (int) $pxml->Items->TotalPages;
            $this->numberResults = count($this->items);
        }

        $products = array();

        foreach( $this->items as $item ) {


            $item_links_raw = (array) $item->ItemLinks->children();
            $item_links_raw  = $item_links_raw['ItemLink'];
            $item_links = array();
            foreach( $item_links_raw as $v=>$item_link ) {
                $item_links[] = (string) $item_link->URL;
            }
            $item_attributes = (array) $item->ItemAttributes;
            $offers = (array) $item->Offers;
            
            $mpn = isset( $item_attributes['MPN'] ) ? $item_attributes['MPN']: '';

            $merchant = '';
            if($item->Offers) {
                $temp = (array)$item->Offers->children();
                $merchant = isset($temp['Offer']) ? (string)$temp['Offer']->Merchant->Name : '';
            }

            $images = array();
            $images['small'] = array(
                'url' => $item->SmallImage->URL,
                'height' => $item->SmallImage->Height,
                'width' => $item->SmallImage->Width,
            );
            $images['medium'] = array(
                'url' => $item->MediumImage->URL,
                'height' => $item->MediumImage->Height,
                'width' => $item->MediumImage->Width,
            );
            $images['large'] = array(
                'url' => $item->LargeImage->URL,
                'height' => $item->LargeImage->Height,
                'width' => $item->LargeImage->Width,
            );

            $related_products = array();

            $similar_products = array();
            foreach ($item->SimilarProducts->children() as $similar_item) {
                if ('SimilarProduct' === $similar_item->getName()) {
                    $similar_products[] = $similar_item;
                }
            }

            $categories = array();
            foreach ($item->BrowseNodes->children() as $node ) {
                if ( 'BrowseNode' === $node->getName()) {
                    if (!empty($node->Ancestors->children)) {
                        $ancestor_categories = $this->get_categories($categories, (array) $node->Ancestors->children, array());
                    }
                    $categories[] = new AmazonCategory((string)$node->BrowseNodeId, (string)$node->Name, $ancestor_categories);
                }
            }

            // RRF
            /*
             *         /*
         * Rank - the 5% secret
    24,000,000 products in x
    Product is ranked #28
    28 / 24,000,000 = %5 or under !!! high seller
 Use () in product search to get all products
         */
            $rrfs = $this->get_rrfs( $item, array(), $categories );

            $product = new \kdaviesnz\amazon\AmazonProduct(
                (string)$item->ASIN, //1
                (string)$item->DetailPageURL,
                (array)$item_links,
                (int)$item->SalesRank,
                (string)isset($item_attributes['Author'][1])?(string)$item_attributes['Author'][1]:'', // 5
                (string)isset($item_attributes['Binding'])?$item_attributes['Binding']:'',
                (string)isset($item_attributes['Brand'])?$item_attributes['Brand']:'',
                (string)isset($item_attributes['Department'])?$item_attributes['Department']:'',
                (string)isset($item_attributes['Color'])?$item_attributes['Color']:'',
                (string)isset($item_attributes['EAN'])?$item_attributes['EAN']:'',//10
                isset($item_attributes['Feature'])?(array)$item_attributes['Feature']:array(),
                (string)isset($item_attributes['Genre'])?$item_attributes['Genre']:'',
                (bool)isset($item_attributes['IsAdultProduct']) && $item_attributes['IsAdultProduct'] == 1,
                (bool)isset($item_attributes['IsAutographed']) && $item_attributes['IsAutographed'] == 1,
                (bool)isset($item_attributes['IsMemorabilia']) && $item_attributes['IsMemorabilia'] == 1,
                isset($item_attributes['ItemDimensions'])?(array)$item_attributes['ItemDimensions']:array(),
                (string)isset($item_attributes['Label'])?$item_attributes['Label']:'',
                isset($item_attributes['Languages'])?(array)$item_attributes['Languages']:array(),
                isset($item_attributes['ListPrice'])?(string)$item_attributes['ListPrice']->Amount:'',
                isset($item_attributes['ListPrice'])?(string)$item_attributes['ListPrice']->CurrencyCode:'',// 20
                isset($item_attributes['ListPrice'])?(string)$item_attributes['ListPrice']->FormattedPrice:'',
                (string)isset($item_attributes['Manufacturer'])?$item_attributes['Manufacturer']:'',
                (string)$item_attributes['ProductGroup'],
                (string)$item_attributes['ProductTypeName'],
                (string)isset($item_attributes['Publisher'])?$item_attributes['Publisher']:'',
                (string)$item_attributes['Title'],
                isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestNewPrice->Amount: '',
                isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestNewPrice->CurrencyCode:'',
                isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestNewPrice->FormattedPrice:'',
                isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestUsedPrice->Amount:'', // 30
                isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestUsedPrice->CurrencyCode:'',
                isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestUsedPrice->FormattedPrice:'',
                isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestCollectiblePrice->Amount:'',
                isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestCollectiblePrice->CurrencyCode:'',
                isset($item_attributes['OfferSummary'])?(string)$item_attributes['OfferSummary']->LowestCollectiblePrice->FormattedPrice:'',
                $images,
                $similar_products,
                $categories,
                $rrfs, // 39
                $mpn,
                $merchant,
                $warranty,
                $image_sets,
                (float) $offers['Offer']->OfferListing->AmountSaved->Amount,
                (string) $offers['Offer']->OfferListing->Availability,
                (string) '0' == $offers['Offer']->OfferListing->IsEligibleForSuperSaverShipping ? 'Not available for free shipping' : 'Available for free shipping',
                (string) $item_links['All Customer Reviews'],
                (string) $item->EditorialReviews->EditorialReview->Content
            );

            //  $product->add_to_database();
            AmazonCache::cacheProduct( $product, '', $related_products );
            $products[] = $product;

        }

        return array(
            'items' => $products,
        );
    }


}