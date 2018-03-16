<?php

include_once('vendor/autoload.php');
include('src/config.php');
include('src/iamazondb.php');
include('src/amazondb.php');
include('src/ioption.php');
include('src/option.php');
include('src/isetting.php');
include('src/setting.php');
include('src/isettings.php');
include('src/amazonsettings.php');
include('src/iamazonproductsearch.php');
include('src/amazonproductsearch.php');
include('src/iamazoncache.php');
include('src/amazoncache.php');
include('src/iamazonparser.php');
include('src/amazonparser.php');
include('src/iamazoncategory.php');
include('src/amazoncategory.php');
include('src/iamazonproduct.php');
include('src/amazonproduct.php');
include('src/iamazonsort.php');
include('src/amazonsort.php');
include('src/iamazonfilter.php');
include('src/amazonfilter.php');
include('src/iamazonkeywordstool.php');
include('src/amazonkeywordstool.php');
include('src/iamazonkeyword.php');
include('src/amazonkeyword.php');
include('src/iamazongeo.php');
include('src/amazongeo.php');

class AmazonProductSearchTest extends PHPUnit_Framework_TestCase {


	public function testAmazonProductSearch() {


		$args = array(
			'amazon_unique_name' => \kdaviesnz\amazon\Option::getOption("amazon_unique_name"),
			'amazon_secret_access_key' => \kdaviesnz\amazon\Option::getOption("amazon_secret_access_key"),
			'amazon_access_key_id' => \kdaviesnz\amazon\Option::getOption("amazon_access_key_id"),
			'amazon_affiliate_link' => \kdaviesnz\amazon\Option::getOption("amazon_affiliate_link")
		);

		\kdaviesnz\amazon\AmazonSettings::add_amazon_account(
			$args
		);

		$amazon_accounts = \kdaviesnz\amazon\AmazonSettings::amazon_accounts()->value();

		// Item test
		$result = \kdaviesnz\amazon\AmazonProductSearch::itemSearch(
			'B00136LUWW'
		);

		var_dump($result);

		$this->assertTrue( !empty( $result ), "true didn't end up being false!" );
		$this->assertTrue( 'B00136LUWW' === $result->getAsin(), "Asin not set." );

		// Similar products.
		$similar_products = $result->frequently_bought_together();
		$this->assertTrue( !empty( $similar_products ), "failed fetching similar products!" );

		// Related products.
//		$related_products = $result->related_products( 'Tracks' );
//		$this->assertTrue( !empty( $related_products ), "failed fetching related products!" );

		// Search
		$to = 1;
		$result = \kdaviesnz\amazon\AmazonProductSearch::search(
			'cats',
			$to,
			\kdaviesnz\amazon\AmazonSort::sortByBest(),
			\kdaviesnz\amazon\AmazonFilter::noFilter(),
			'',
			'All'
		);


		$this->assertTrue( !empty( $result ), "true didn't end up being false!" );

	}

}
