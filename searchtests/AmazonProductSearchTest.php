<?php

include_once( 'vendor/autoload.php' );
include( 'src/iamazongeo.php');
include( 'src/amazongeo.php');
include( 'src/iamazonkeyword.php');
include( 'src/amazonkeyword.php');
include( 'src/iamazonkeywordstool.php');
include( 'src/amazonkeywordstool.php');
include( 'src/iamazonfilter.php');
include( 'src/amazonfilter.php');
include( 'src/iamazonsort.php');
include( 'src/amazonsort.php');
include( 'src/iamazonproduct.php' );
include( 'src/amazonproduct.php' );
include( 'src/iamazoncategory.php' );
include( 'src/amazoncategory.php' );
include( 'src/iamazonparser.php' );
include( 'src/amazonparser.php' );
include( 'src/iamazoncache.php' );
include( 'src/amazoncache.php' );
include( 'src/iamazondb.php' );
include( 'src/amazondb.php' );
include( 'src/itransient.php' );
include( 'src/transient.php' );
include( 'src/iamazonproductsearch.php' );
include( 'src/amazonproductsearch.php' );
include( 'src/ioption.php' );
include( 'src/option.php' );
include( 'src/isetting.php' );
include( 'src/setting.php' );
include( 'src/isettings.php' );
include( 'src/amazonsettings.php' );

class AmazonProductSearchTest extends PHPUnit_Framework_TestCase {


	public function testAmazonProductSearch() {

		global $conn;
		global $options;
		include("src/config.php");


		\kdaviesnz\amazon\AmazonSettings::reset();
		\kdaviesnz\amazon\AmazonSettings::add_amazon_account(
			array(
				'amazon_unique_name' => '',
				'amazon_secret_access_key' => '',
				'amazon_access_key_id' => '',
				'amazon_affiliate_link' => ''
			)
		);

		$amazon_accounts = \kdaviesnz\amazon\AmazonSettings::amazon_accounts()->value();

		// Item test
		$skuProduct  = \kdaviesnz\amazon\AmazonProductSearch::itemSearch(
			'B00136LUWW',
			'SKU'
		);

		$salesData = array(
			"30days" => 19.99,
			"6months" => 54.00,
			"12months" => 120.00,
			"30daysSalesCount" => 10,
			"6monthsSalesCount" => 40,
			"12monthsSalesCount" => 80,
		);
		$skuProduct->addSalesData($salesData);

		// Item test
		$result = \kdaviesnz\amazon\AmazonProductSearch::itemSearch(
			'B00136LUWW',
			'ASIN'
		);

		$this->assertTrue( !empty( $result ), "true didn't end up being false!" );
		$this->assertTrue( 'B00136LUWW' === $result->getAsin(), "Asin not set." );



		// Similar products.
		//$similar_products = $result->frequently_bought_together();
		//$this->assertTrue( !empty( $similar_products ), "failed fetching similar products!" );

		// Related products.
		//$related_products = $result->related_products( 'Tracks' );
		//$this->assertTrue( !empty( $related_products ), "failed fetching related products!" );

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
