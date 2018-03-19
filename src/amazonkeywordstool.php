<?php


namespace kdaviesnz\amazon;


/**
 * Class AmazonKeywordsTool
 *
 * @package kdaviesnz\amazon
 */
class AmazonKeywordsTool implements IAmazonKeywordsTool
{

	public static function get_best_keywords() {

		global $conn;
		$transient = new Transient($conn);

		$keywords = $transient->fetch('bestkeywords', 3600 * 24 * 7 );

		if ( $keywords === false ) {

			$keywords = array();

			global $wpdb;
			$sql = $wpdb->prepare(
				"SELECT `searchTerm`, AVG( `wp_amazon_amazon_products`.`avgRRF` ) as `rrf` FROM `wp_ama//zon_search`, `wp_amazon_amazon_products` WHERE `wp_amazon_search`.`AIN` = `wp_amazon_amazon_products`.`AIN` AND `wp_amazon_amazon_products`.`avgRRF` > 0
			GROUP BY `wp_amazon_search`.`searchTerm` ORDER BY `rrf`",
				''
			);

			$keywords = array();
			$records = $wpdb->get_results($sql);

			foreach ($records as $record) {
				$keywords[] = AmazonCache::getKeywords($record->searchTerm, $record->rrf * 1.00);
			}

			$transient->save('bestkeywords', 3600 * 24 * 7);

		}

		return $keywords;

	}

	/**
	 * Fetch keywords.
	 *
	 * @param string $keyword
	 * @param string $ip
	 * @return array
	 */
	public static function fetch( $phrase, $ip, $depth = 1, $max_depth = 2 ) {


		$keywords = AmazonCache::getKeywords( $phrase, 0.00, 1 );

		if ( ! empty( $keywords[$phrase]->variations ) ) {

			return $keywords;

		} else {

			$geo = new AmazonGeo($ip);
			$categories = array();

			$base_url = 'https://completion.amazon.com/search/complete?method=completion&mkt=1&l=' . $geo->getAmazonLang() . '&client=amazon-search-ui&search-alias=aps';


			// Get keyword information.
			$url = $base_url . '&q=' . $phrase;

			// Docs: http://guzzlephp.org/.
			$headers = array();
			$client = new \GuzzleHttp\Client();
			$request = new \GuzzleHttp\Psr7\Request('GET', $url, $headers);

			$result = $client->send($request);

			$temp = json_decode($result->getBody()->getContents());

			$variation_keywords = isset($temp[1]) ? $temp[1]:array();
			// $category_objs = isset( $temp[2] ) ? $temp[2]();
			if (!empty($temp[2]) && is_object($temp[2][0]) && property_exists($temp[2][0], 'nodes')) {
				foreach ($temp[2][0]->nodes as $category) {
					$categories[] = $category;
				}
			}


			$variations = array();
			if ($depth < $max_depth) {
				foreach ($variation_keywords as $variation) {
					if ($variation != $phrase) {
						$variations[$variation] = AmazonKeywordsTool::fetch($variation, $ip, $depth + 1, $max_depth);
					}
				}
			}

			$keyword = new AmazonKeyword( $phrase, $categories, $variations, 0.00 );

			AmazonCache::cacheKeywords( $keyword );

			return array($phrase => $keyword);

		}
		//return $keywords;
	}

	/**
	 * Fetch keywords recursively.
	 *
	 * @param string $base_url
	 * @param string $new_keyword
	 * @param array $keywords
	 * @param int $depth
	 * @param int $max_depth
	 * @return array
	 */
	private static function fetch_keywords ( $base_url, $new_keyword, $keywords, $depth, $max_depth, $ip ) {

		$url = $base_url . '&q='  . $new_keyword;

		// Docs: http://guzzlephp.org/.
		$headers = array();
		$client = new \GuzzleHttp\Client();
		$request = new \GuzzleHttp\Psr7\Request( 'GET', $url, $headers );

		$result = $client->send( $request );

		$temp = json_decode( $result->getBody()->getContents() );

		if ( isset( $temp[0] ) ) {

			$categories = array();
			if ( isset( $temp[2] ) && is_array( $temp[2][0]->nodes ) )  {
				foreach ( $temp[2][0]->nodes as $category ) {
					$categories[] = $category;
				}
			}
			$keyword = new \stdClass();
			$keyword->name = $temp[0];
			$keyword->categories = $categories;

			$keyword->variations = array();

			foreach ( $temp[1] as $keyword_name ) {
				if ( $keyword->name !== $keyword_name ) {

					$url = $base_url . '&q='  . $keyword_name;

					// Docs: http://guzzlephp.org/.
					$request = new \GuzzleHttp\Psr7\Request( 'GET', $url, $headers );
					$result = $client->send( $request );
					$t = json_decode( $result->getBody()->getContents() );

					if ( isset( $t[0])) {

						$categories = array();
						if ( isset( $t[2] ) && is_array( $t[2][0]->nodes ) )  {
							foreach ( $t[2][0]->nodes as $category ) {
								$categories[] = $category;
							}
						}
						$v = new \stdClass();
						$v->name = $temp[0];
						$v->categories = $categories;

						$keyword->variations[$keyword_name] = $v;
					}


				//	$keyword->variations = AmazonKeywordsTool::fetch($keyword_name, $ip);
				}
			}

			$keywords[$new_keyword] = $keyword;
		}

		return $keywords;

	}
	/**
	 * Fetch keywords recursively.
	 *
	 * @param string $base_url
	 * @param string $new_keyword
	 * @param array $keywords
	 * @param int $depth
	 * @param int $max_depth
	 * @return array
	 */
	private static function fetch_keywords_old ( $base_url, $new_keyword, $keywords, $depth, $max_depth ) {

		$url = $base_url . '&q='  . $new_keyword;

		// Docs: http://guzzlephp.org/.
		$headers = array();
		$client = new \GuzzleHttp\Client();
		$request = new \GuzzleHttp\Psr7\Request( 'GET', $url, $headers );

		$result = $client->send( $request );

		$temp = json_decode( $result->getBody()->getContents() );

		if ( isset( $temp[0] ) ) {
			$categories = array();
			if ( isset( $temp[2] ) ) {
				foreach ( $temp[2][0]->nodes as $category ) {
					$categories[] = $category;
				}
			}
			$keyword = new \stdClass();
			$keyword->name = $temp[0];
			$keyword->categories = $categories;
			$keyword->variations = array();
			$keywords[$new_keyword] = $keyword;
		}

		if ( $depth < $max_depth && ! empty( $temp[1] ) ) {
			$depth++;
			foreach ( $temp[1] as $keyword ) {
					$keywords = AmazonKeywordsTool::fetch_keywords($base_url, $keyword, $keywords, $depth, $max_depth);

			}
		}

		return $keywords;

	}


}
