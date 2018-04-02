<?php
/**
 * Created by PhpStorm.
 * User: kevindavies
 * Date: 26/03/18
 * Time: 1:55 PM
 */

namespace kdaviesnz\amazon;
  

interface IAmazonMWSProductInfo {

	// These methods are set by the parent class
	/**
	 * Sets the feed seller SKU(s). (Required*)
	 *
	 * This method sets the list of seller SKUs to be sent in the next request.
	 * Setting this parameter tells Amazon to only return inventory supplies that match
	 * the IDs in the list. If this parameter is set, ASINs cannot be set.
	 * @param array|string $s <p>A list of Seller SKUs, or a single SKU string. (max: 20)</p>
	 * @return boolean <b>FALSE</b> if improper input
	 */
	public function setSKUs($s);

	/**
	 * Sets the ASIN(s). (Required*)
	 *
	 * This method sets the list of ASINs to be sent in the next request.
	 * Setting this parameter tells Amazon to only return inventory supplies that match
	 * the IDs in the list. If this parameter is set, Seller SKUs cannot be set.
	 * @param array|string $s <p>A list of ASINs, or a single ASIN string. (max: 20)</p>
	 * @return boolean <b>FALSE</b> if improper input
	 */
	public function setASINs($s);

	/**
	 * Sets the item condition filter. (Optional)
	 *
	 * This method sets the item condition filter to be sent in the next request.
	 * Setting this parameter tells Amazon to only return products with conditions that match
	 * the one given. If this parameter is not set, Amazon will return products with any condition.
	 * @param string $s <p>Single condition string.</p>
	 * @return boolean <b>FALSE</b> if improper input
	 */
	public function setConditionFilter($s);

	/**
	 * Sets the "ExcludeSelf" flag. (Optional)
	 *
	 * Sets whether or not the next Lowest Offer Listings request should exclude your own listings.
	 * @param string|boolean $s <p>"true" or "false", or boolean</p>
	 * @return boolean <b>FALSE</b> if improper input
	 */
	public function setExcludeSelf($s = 'true');

	/**
	 * Fetches a list of competitive pricing on products from Amazon.
	 *
	 * Submits a <i>GetCompetitivePricingForSKU</i>
	 * or <i>GetCompetitivePricingForASIN</i> request to Amazon. Amazon will send
	 * the list back as a response, which can be retrieved using <i>getProduct</i>.
	 * @return boolean <b>FALSE</b> if something goes wrong
	 */
	public function fetchCompetitivePricing();

	/**
	 * Fetches a list of lowest offers on products from Amazon.
	 *
	 * Submits a <i>GetLowestOfferListingsForSKU</i>
	 * or <i>GetLowestOfferListingsForASIN</i> request to Amazon. Amazon will send
	 * the list back as a response, which can be retrieved using <i>getProduct</i>.
	 * @return boolean <b>FALSE</b> if something goes wrong
	 */
	public function fetchLowestOffer();

	/**
	 * Fetches a list of lowest offers on products from Amazon.
	 *
	 * Submits a <i>GetLowestPricedOffersForSKU</i>
	 * or <i>GetLowestPricedOffersForASIN</i> request to Amazon. Amazon will send
	 * the list back as a response, which can be retrieved using <i>getProduct</i>.
	 * @return boolean <b>FALSE</b> if something goes wrong
	 */
	public function fetchLowestPricedOffers();


	/**
	 * Fetches a list of your prices on products from Amazon.
	 *
	 * Submits a <i>GetMyPriceForSKU</i>
	 * or <i>GetMyPriceForASIN</i> request to Amazon. Amazon will send
	 * the list back as a response, which can be retrieved using <i>getProduct</i>.
	 * @return boolean <b>FALSE</b> if something goes wrong
	 */
	public function fetchMyPrice();

	/**
	 * Fetches a list of categories for products from Amazon.
	 *
	 * Submits a <i>GetProductCategoriesForSKU</i>
	 * or <i>GetProductCategoriesForASIN</i> request to Amazon. Amazon will send
	 * the list back as a response, which can be retrieved using <i>getProduct</i>.
	 * @return boolean <b>FALSE</b> if something goes wrong
	 */
	public function fetchCategories();


}