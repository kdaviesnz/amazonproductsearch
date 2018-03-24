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
include('src/itransient.php');
include('src/transient.php');

// MWS
// cpigroup/php-amazon-mws
// https://packagist.org/packages/cpigroup/php-amazon-mws

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

// sales data
// http://www.ifourtechnolab.com/blog/estimated-sales-report-generation-through-amazon-api-mws-marketplace-api
// https://www.quora.com/How-do-I-get-amazon-product-sales-data
// https://developer.amazonservices.com/index.html/130-0904142-4129506


// look up by UPC
/*
 https://docs.aws.amazon.com/AWSECommerceService/latest/DG/EX_LookupbyUPC.html
https://docs.aws.amazon.com/AWSECommerceService/latest/DG/OtherItemIdentifiers.html

 http://webservices.amazon.com/onca/xml?
  Service=AWSECommerceService
  &Operation=ItemLookup
  &ResponseGroup=Large
  &SearchIndex=All
  &IdType=UPC
  &ItemId=635753490879
  &AWSAccessKeyId=[Your_AWSAccessKeyID]
  &AssociateTag=[Your_AssociateTag]
  &Timestamp=[YYYY-MM-DDThh:mm:ssZ]
  &Signature=[Request_Signature]
 */

// look up by EAN


// http://docs.aws.amazon.com/AWSECommerceService/latest/DG/Motivating_RelatedItems.html#RelationshipTypes

// 'B00136LUWW'
// https://www.amazon.com/Apple-13-3-MacBook-Air-Silver/dp/B015WXL0C6/ref=sr_1_2?s=pc&ie=UTF8&qid=1521435146&sr=1-2&refinements=p_n_operating_system_browse-bin%3A7529233011

// Item test
$result = \kdaviesnz\amazon\AmazonProductSearch::itemSearch(
	'635753490879',
	'UPC'
);


// Similar products.
$similar_products = $result->frequently_bought_together();

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


