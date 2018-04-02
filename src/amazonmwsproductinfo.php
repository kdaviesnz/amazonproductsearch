<?php
declare( strict_types=1 ); // must be first line

namespace vendorname\subnamespace; // use vendorname\subnamespace\classname;

namespace kdaviesnz\amazon;


class AmazonMWSProductInfo extends \AmazonProductInfo implements IAmazonMWSProductInfo {


	/**
	 * AmazonProductInfo fetches a list of info from Amazon.
	 *
	 * The parameters are passed to the parent constructor, which are
	 * in turn passed to the AmazonCore constructor. See it for more information
	 * on these parameters and common methods.
	 * @param string $s [optional] <p>Name for the store you want to use.
	 * This parameter is optional if only one store is defined in the config file.</p>
	 * @param boolean $mock [optional] <p>This is a flag for enabling Mock Mode.
	 * This defaults to <b>FALSE</b>.</p>
	 * @param array|string $m [optional] <p>The files (or file) to use in Mock Mode.</p>
	 * @param string $config [optional] <p>An alternate config file to set. Used for testing.</p>
	 */
	public function __construct($s = null, $mock = false, $m = null, $config = null){
		parent::__construct($s, $mock, $m, $config);
	}

	/**
	 * Fetches a list of lowest offers on products from Amazon.
	 *
	 * Submits a <i>GetLowestPricedOffersForSKU</i>
	 * or <i>GetLowestPricedOffersForASIN</i> request to Amazon. Amazon will send
	 * the list back as a response, which can be retrieved using <i>getProduct</i>.
	 * @return boolean <b>FALSE</b> if something goes wrong
	 */
	public function fetchLowestPricedOffers() {

		// @see http://docs.developer.amazonservices.com/en_US/products/Products_GetLowestPricedOffersForASIN.html
		$lowestPricedOffers = parent::fetchLowestPricedOffers();
		if ( ! empty( $this->getLastErrorMessage() ) ) {
			return false;
		}

		return $lowestPricedOffers;

	}

	/**
	 * Fetches a list of competitive pricing on products from Amazon.
	 *
	 * Submits a <i>GetCompetitivePricingForSKU</i>
	 * or <i>GetCompetitivePricingForASIN</i> request to Amazon. Amazon will send
	 * the list back as a response, which can be retrieved using <i>getProduct</i>.
	 * @return boolean <b>FALSE</b> if something goes wrong
	 */
	public function fetchCompetitivePricing() {

		// @see http://docs.developer.amazonservices.com/en_US/products/Products_GetCompetitivePricingForASIN.html
		$competitivePricing = parent::fetchCompetitivePricing();
		if ( ! empty( $this->getLastErrorMessage() ) ) {
			return false;
		}

		return $competitivePricing;
	}

}