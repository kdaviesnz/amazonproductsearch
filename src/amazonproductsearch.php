<?php
declare(strict_types=1);


namespace kdaviesnz\amazon;

// see vendor/exeu

use ApaiIO\Configuration\BestProductsConfiguration;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\BestProductsSearch;
use ApaiIO\Operations\Lookup;
use ApaiIO\Operations\Search;
use ApaiIO\ApaiIO;

class AmazonProductSearch implements IAmazonProductSearch
{

	public static function getPostRelatedProducts( $wp_post, $to, $sortFn, $filterFn, $relationshipType = '', $searchIndex =' All', $search = null, $from = null, $endPoint = 'webservices.amazon.com', $uri = '/onca/xml' ) {
		return array();
	}

	public static function mostRecentProductsByCategorySearch( $categoryID, $relationshipType='', $endPoint = 'webservices.amazon.com', $uri = '/onca/xml' ) {

		$groups = array( 'BrowseNodeInfo', 'NewReleases' );
		return categorySearch( $categoryID, $groups, $relationshipType, $endPoint, $uri );

	}

	public static function bestProductsByCategorySearch( $categoryID, $relationshipType='', $endPoint = 'webservices.amazon.com', $uri = '/onca/xml' ) {

		$groups = array( 'BrowseNodeInfo', 'BestSellers' );
		return categorySearch( $categoryID, $groups, $relationshipType, $endPoint, $uri );

	}

	public static function bestProductsSearch( $searchTerm, $to, $searchIndex, $filterFn, $relationshipType = '', $from = null, $endPoint = 'webservices.amazon.com', $uri = '/onca/xml')
	{
		$search = new BestProductsSearch();
		return AmazonProductSearch::search(  $searchTerm,  $to, AmazonSort::sortByBest(), $filterFn, $relationshipType, $searchIndex, $search, $from, $endPoint, $uri );

	}

    public static function search( $searchTerm, $to, $sortFn, $filterFn, $relationshipType = '', $searchIndex =' All', $search = null, $from = null, $endPoint = 'webservices.amazon.com', $uri = '/onca/xml')
    {
    	// @todo
		//$data = get_transient('searchresults' . $searchTerm . $to . $relationshipType . $searchIndex . get_class($search) );
		$data = false;
		if ( $data == false || count( $data ) < 40  ) {

			$groups = array('Large', 'Accessories', 'BrowseNodes', 'Images', 'ItemAttributes', 'SalesRank', 'Similarities', 'Variations', 'SalesRank', 'OfferFull', 'EditorialReview');


			$search = empty($search) ? new Search() : $search;
			$conf = new GenericConfiguration();

			// Save search term as keyword - this also caches the keyword
			// @todo
			//$phrases = get_transient('phrasessearch' .$searchTerm);
			$phrases = false;

			if ($phrases == false) {
				$phrase_tree_array = AmazonKeywordsTool::fetch($searchTerm, '');
				$phrases = AmazonProductSearch::get_phrases(array(), $phrase_tree_array);
				// @todo
				//set_transient('phrasessearch' .$searchTerm, $phrases, 3600 * 24 * 7 ); // Cache for seven days.
			}

			$phrases = array_slice( $phrases, 0, 1 );

			$products_found = array();

			$amazon_accounts = AmazonSettings::amazon_accounts()->value();
			$aws_access_key_id = $amazon_accounts[0]['amazon_access_key_id'];
			$aws_secret_key = $amazon_accounts[0]['amazon_secret_access_key'];
			$affiliate_tag = $amazon_accounts[0]['amazon_affiliate_link'];

			$client = new \GuzzleHttp\Client();
			$request = new \ApaiIO\Request\GuzzleRequest($client);

			$conf
				->setCountry('com')
				->setAccessKey($aws_access_key_id)
				->setSecretKey($aws_secret_key)
				->setAssociateTag($affiliate_tag)
				->setRequest($request);
			$apaiIO = new ApaiIO($conf);

			$search->setCategory($searchIndex);

			// Relationship types: Episode, Season, Tracks, and Variation (http://docs.aws.amazon.com/AWSECommerceService/latest/DG/Motivating_RelatedItems.html#RelationshipTypes)
			if ($relationshipType != '' && !empty($relationshipType)) {
				$groups[] = 'RelatedItems';
				$search->setRelationshipType($relationshipType);
			}

			$search->setResponseGroup($groups);


			foreach ($phrases as $phrase) {

				//var_dump($phrase);
				//die();

				try {
					// Look in database
					$products = AmazonCache::performCachedSearch((string)$phrase, get_class($search));
					AmazonCache::cacheSearch((string) $phrase, $products, get_class($search));
					$products_found = array_merge($products_found, $products);
					throw new \Exception('testing');
				} catch (\Exception $e) {

					// @todo
					// $search_results_xml = get_transient('searchresultsxml' . $to . $relationshipType . $phrase . get_class($search));
					$search_results_xml = false;

					if (!empty($search_results_xml)) {
						// header( 'Content-Type:application/xml' );
						//echo $search_results_xml;
						//die();
						// Parse results.
						//$parser = new AmazonParser();
						//$results = $parser->parse_search_results($search_results_xml);
					} else{
						$search->setKeywords($phrase);
						$search_results_xml = $apaiIO->runOperation($search);
					}

					//header( 'Content-Type:application/xml' );
					//echo $search_results_xml;
					//die();

					//Parse results.
					$parser = new AmazonParser();
					$results = $parser->parse_search_results($search_results_xml);

					if( isset($results['items'])) {
						AmazonCache::cacheSearch((string)$phrase, $results['items'], get_class($search));
						$products_found = array_merge($products_found, $results['items']);
						//	set_transient('searchresultsxml' . $to . $relationshipType . $phrase . get_class($search), $search_results_xml, 3600 * 24); // Cache for one day.
					}

					// @todo
				//	set_transient('searchresults' . $phrase . $to . $relationshipType . $searchIndex . get_class($search), $results['items'], 3600 * 24 ); // Cache for one day.

				}

			}

			$data = $products_found;

		// @todo
		//	set_transient('searchresults' . $searchTerm . $to . $relationshipType . $searchIndex . get_class($search), $results['items'], 3600 * 24 ); // Cache for one day.

		}


		// $data = $sortFn($filterFn($data));

		$data = ($filterFn($data)); // @todo

		return $data;

    }


    public static function itemSearch( $id, $idType, $relationshipType='', $endPoint = 'webservices.amazon.com', $uri = '/onca/xml' )
	{

		global $conn;
		$transient = new Transient($conn);
		$search_results_xml = $transient->fetch('itemsearchresultsxml' . $id .  $relationshipType);

		if ($search_results_xml != false) {
			$parser = new AmazonParser();
			$results = $parser->parse_item_search_results($search_results_xml);
			$product = $results['items'][0];

			$related_products = array();

			AmazonCache::cacheProduct($product, $relationshipType, $related_products);

		} else {

			try {
				$product = AmazonCache::getProduct($id, $relationshipType);
				throw new \Exception("Just testing");
			} catch (\Exception $e) {


				$conf = new GenericConfiguration();
				$client = new \GuzzleHttp\Client();
				$request = new \ApaiIO\Request\GuzzleRequest($client);

				$amazon_accounts = AmazonSettings::amazon_accounts()->value();

				$aws_access_key_id = $amazon_accounts[0]['amazon_access_key_id'];
				$aws_secret_key = $amazon_accounts[0]['amazon_secret_access_key'];
				$affiliate_tag = $amazon_accounts[0]['amazon_affiliate_link'];

				$conf
					->setCountry('com')
					->setAccessKey($aws_access_key_id)
					->setSecretKey($aws_secret_key)
					->setAssociateTag($affiliate_tag)
					->setRequest($request);
				$apaiIO = new ApaiIO($conf);

				$lookup = new \ApaiIO\Operations\Lookup();

				// Relationship types: Episode, Season, Tracks, and Variation (http://docs.aws.amazon.com/AWSECommerceService/latest/DG/Motivating_RelatedItems.html#RelationshipTypes)
				$groups = array('Large', 'Accessories', 'BrowseNodes', 'Images', 'ItemAttributes', 'SalesRank', 'Similarities', 'Variations', 'SalesRank', 'OfferFull', 'EditorialReview');
				if ($relationshipType != '' && !empty($relationshipType)) {
					$groups[] = 'RelatedItems';
					$lookup->setRelationshipType($relationshipType);
				}

				$lookup->setResponseGroup($groups);
				$lookup->setIdType($idType);
				$lookup->setItemId($id);

				$search_results_xml = $apaiIO->runOperation($lookup);
				//header( 'Content-Type:application/xml' );

				//echo $search_results_xml;
				//die();
				// Parse results.
				$parser = new AmazonParser();
				$results = $parser->parse_item_search_results($search_results_xml);
				$product = $results['items'][0];

				$related_products = array();

				AmazonCache::cacheProduct($product, $relationshipType, $related_products);

				$transient->save('itemsearchresultsxml' . $id .  $relationshipType, $search_results_xml, 3600 * 24 * 1 ); // Cache for one days.

			}

		}

        return $product;

    }


	/**
	 * Get items in a category
	 * @param string $categoryID
	 * @param array $groups
	 * @param string $relationshipType
	 * @param string $endPoint
	 * @param string $uri
	 * @return array
	 */
	public static function categorySearch($categoryID, $groups, $relationshipType='', $endPoint = 'webservices.amazon.com', $uri = '/onca/xml')
	{

		// @todo
//		$category_search_results_xml = get_transient('categorysearchresults' . $categoryID .  implode('',$groups). $relationshipType);
		$category_search_results_xml = false;

		if ($category_search_results_xml != false) {

			try {
				$parser = new AmazonParser();
				$results = $parser->parse_category_search_results( $category_search_results_xml );

			} catch (Exception $e) {
				$data = array('total_results' => 0, 'items' => array(), 'error' => $e->getMessage());
			}

			$data = array('total_results' => $parser->totalResults, 'items' => $results['items']);


		} else {

			try {
				$category = AmazonCache::getCategory($categoryID);
				$data = array('total_results' => $category->get_number_of_items(), 'items' => array());

			} catch (\Exception $e) { // Category has not be cached or is out of date.

				$conf = new GenericConfiguration();
				$client = new \GuzzleHttp\Client();
				$request = new \ApaiIO\Request\GuzzleRequest($client);

				$amazon_accounts = AmazonSettings::amazon_accounts()->value();
				$aws_access_key_id = $amazon_accounts[0]['amazon_access_key_id'];
				$aws_secret_key = $amazon_accounts[0]['amazon_secret_access_key'];
				$affiliate_tag = $amazon_accounts[0]['amazon_affiliate_link'];

				$conf
					->setCountry('com')
					->setAccessKey($aws_access_key_id)
					->setSecretKey($aws_secret_key)
					->setAssociateTag($affiliate_tag)
					->setRequest($request);
				$apaiIO = new ApaiIO($conf);

				$search = new Search();
				$search->setCategory('Appliances');
				$search->setBrowseNode($categoryID);

				// Relationship types: Episode, Season, Tracks, and Variation (http://docs.aws.amazon.com/AWSECommerceService/latest/DG/Motivating_RelatedItems.html#RelationshipTypes)
				if ($relationshipType != '' && !empty($relationshipType)) {
					$groups[] = 'RelatedItems';
					$search->setRelationshipType($relationshipType);
				}

				$search->setResponseGroup($groups);

				try {
					$category_search_results_xml = $apaiIO->runOperation($search);
					$parser = new AmazonParser();
					$results = $parser->parse_category_search_results( $category_search_results_xml );
					// Cache the xml.
					// @todo
					//set_transient('categorysearchresults' . $categoryID .  implode('',$groups). $relationshipType, $data, 3600 * 24 * 1 ); // Cache for one days.

				} catch (Exception $e) {
					$data = array('total_results' => 0, 'items' => array(), 'error' => $e->getMessage());
				}

				$data = array('total_results' => $parser->totalResults, 'items' => $results['items']);

			}


		}

		return $data;

	}

	public static function get_phrases( $phrases, $phrase_tree_array ) {

		foreach ($phrase_tree_array as $phrase => $phrase_object) {
			$phrases[] = $phrase;
			if (!empty($phrase_object->variations)) {
				$phrases = AmazonProductSearch::get_phrases($phrases, $phrase_object->variations);
			}
		}


		return $phrases;
	}

	private static function singlePageSearch($page, $secretKey, $endPoint, $uri, $params)
    {

    }
}
