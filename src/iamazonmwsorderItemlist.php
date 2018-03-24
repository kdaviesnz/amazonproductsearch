<?php
/**
 * Created by PhpStorm.
 * User: kevindavies
 * Date: 23/03/18
 * Time: 9:48 AM
 */

namespace kdaviesnz\amazon;


interface IAmazonMWSOrderItemList {

	// These methods are set by the parent class.

	/**
	 * Returns whether or not a token is available.
	 * @return boolean
	 */
	public function hasToken();

	/**
	 * Sets whether or not the object should automatically use tokens if it receives one.
	 *
	 * If this option is set to <b>TRUE</b>, the object will automatically perform
	 * the necessary operations to retrieve the rest of the list using tokens. If
	 * this option is off, the object will only ever retrieve the first section of
	 * the list.
	 * @param boolean $b [optional] <p>Defaults to <b>TRUE</b></p>
	 * @return boolean <b>FALSE</b> if improper input
	 */
	public function setUseToken($b = true);

	/**
	 * Sets the Amazon Order ID. (Required)
	 *
	 * This method sets the Amazon Order ID to be sent in the next request.
	 * This parameter is required for fetching the order's items from Amazon.
	 * @param string $id <p>Amazon Order ID</p>
	 * @return boolean <b>FALSE</b> if improper input
	 */
	public function setOrderId($id);

	/**
	 * Retrieves the items from Amazon.
	 *
	 * Submits a <i>ListOrderItems</i> request to Amazon. In order to do this,
	 * an Amazon order ID is required. Amazon will send
	 * the data back as a response, which can be retrieved using <i>getItems</i>.
	 * Other methods are available for fetching specific values from the order.
	 * This operation can potentially involve tokens.
	 * @param boolean $r [optional] <p>When set to <b>FALSE</b>, the function will not recurse, defaults to <b>TRUE</b></p>
	 * @return boolean <b>FALSE</b> if something goes wrong
	 */
	public function fetchItems($r = true);

	/**
	 * Returns the order ID for the items.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @return string|boolean single value, or <b>FALSE</b> if not set yet
	 */
	public function getOrderId();

	/**
	 * Returns the specified order item, or all of them.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * The array for a single order item will have the following fields:
	 * <ul>
	 * <li><b>ASIN</b> - the ASIN for the item</li>
	 * <li><b>SellerSKU</b> - the SKU for the item</li>
	 * <li><b>OrderItemId</b> - the unique ID for the order item</li>
	 * <li><b>Title</b> - the name of the item</li>
	 * <li><b>QuantityOrdered</b> - the quantity of the item ordered</li>
	 * <li><b>QuantityShipped</b> (optional) - the quantity of the item shipped</li>
	 * <li><b>GiftMessageText</b> (optional) - gift message for the item</li>
	 * <li><b>GiftWrapLevel</b> (optional) - the type of gift wrapping for the item</li>
	 * <li><b>ItemPrice</b> (optional) - price for the item, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
	 * <li><b>ShippingPrice</b> (optional) - price for shipping, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
	 * <li><b>GiftWrapPrice</b> (optional) - price for gift wrapping, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
	 * <li><b>ItemTax</b> (optional) - tax on the item, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
	 * <li><b>ShippingTax</b> (optional) - tax on shipping, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
	 * <li><b>GiftWrapTax</b> (optional) - tax on gift wrapping, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
	 * <li><b>ShippingDiscount</b> (optional) - discount on shipping, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
	 * <li><b>PromotionDiscount</b> (optional) -promotional discount, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
	 * <li><b>CODFee</b> (optional) -fee charged for COD service, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
	 * <li><b>CODFeeDiscount</b> (optional) -discount on COD fee, array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
	 * <li><b>PromotionIds</b> (optional) -array of promotion IDs</li>
	 * </ul>
	 * @param int $i [optional] <p>List index to retrieve the value from.
	 * If none is given, the entire list will be returned. Defaults to NULL.</p>
	 * @return array|boolean array, multi-dimensional array, or <b>FALSE</b> if list not filled yet
	 */
	public function getItems($i = null);

	/**
	 * Returns the ASIN for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getASIN($i = 0);

	/**
	 * Returns the seller SKU for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getSellerSKU($i = 0);

	/**
	 * Returns the order item ID for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getOrderItemId($i = 0);

	/**
	 * Returns the name for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getTitle($i = 0);

	/**
	 * Returns the quantity ordered for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getQuantityOrdered($i = 0);

	/**
	 * Returns the quantity shipped for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getQuantityShipped($i = 0);

	/**
	 * Returns the URL for the ZIP file containing the customized options for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getCustomizedInfo($i = 0);

	/**
	 * Returns the number of Amazon Points granted for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * If an array is returned, it will have the fields <b>PointsNumber</b>, <b>Amount</b> and <b>CurrencyCode</b>.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @param boolean $only [optional] <p>set to <b>TRUE</b> to get only the number of points</p>
	 * @return array|string|boolean array, single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getPointsGranted($i = 0, $only = false);

	/**
	 * Returns the price designation for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getPriceDesignation($i = 0);

	/**
	 * Returns the seller SKU for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return float|boolean decimal number from 0 to 1, or <b>FALSE</b> if Non-numeric index
	 */
	public function getPercentShipped($i = 0);

	/**
	 * Returns the gift message text for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getGiftMessageText($i = 0);

	/**
	 * Returns the gift wrap level for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getGiftWrapLevel($i = 0);

	/**
	 * Returns the item price for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * If an array is returned, it will have the fields <b>Amount</b> and <b>CurrencyCode</b>.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @param boolean $only [optional] <p>set to <b>TRUE</b> to get only the amount</p>
	 * @return array|string|boolean array, single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getItemPrice($i = 0, $only = false);

	/**
	 * Returns the shipping price for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * If an array is returned, it will have the fields <b>Amount</b> and <b>CurrencyCode</b>.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @param boolean $only [optional] <p>set to <b>TRUE</b> to get only the amount</p>
	 * @return array|string|boolean array, single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getShippingPrice($i = 0, $only = false);

	/**
	 * Returns the gift wrap price for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * If an array is returned, it will have the fields <b>Amount</b> and <b>CurrencyCode</b>.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @param boolean $only [optional] <p>set to <b>TRUE</b> to get only the amount</p>
	 * @return array|string|boolean array, single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getGiftWrapPrice($i = 0, $only = false);

	/**
	 * Returns the item tax for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * If an array is returned, it will have the fields <b>Amount</b> and <b>CurrencyCode</b>.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @param boolean $only [optional] <p>set to <b>TRUE</b> to get only the amount</p>
	 * @return array|string|boolean array, single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getItemTax($i = 0, $only = false);

	/**
	 * Returns the shipping tax for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * If an array is returned, it will have the fields <b>Amount</b> and <b>CurrencyCode</b>.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @param boolean $only [optional] <p>set to <b>TRUE</b> to get only the amount</p>
	 * @return array|string|boolean array, single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getShippingTax($i = 0, $only = false);

	/**
	 * Returns the gift wrap tax for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * If an array is returned, it will have the fields <b>Amount</b> and <b>CurrencyCode</b>.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @param boolean $only [optional] <p>set to <b>TRUE</b> to get only the amount</p>
	 * @return array|string|boolean array, single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getGiftWrapTax($i = 0, $only = false);


	/**
	 * Returns the shipping discount for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * If an array is returned, it will have the fields <b>Amount</b> and <b>CurrencyCode</b>.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @param boolean $only [optional] <p>set to <b>TRUE</b> to get only the amount</p>
	 * @return array|string|boolean array, single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getShippingDiscount($i = 0, $only = false);

	/**
	 * Returns the promotional discount for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * If an array is returned, it will have the fields <b>Amount</b> and <b>CurrencyCode</b>.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @param boolean $only [optional] <p>set to <b>TRUE</b> to get only the amount</p>
	 * @return array|string|boolean array, single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getPromotionDiscount($i = 0, $only = false);

	/**
	 * Returns specified promotion ID for the specified item.
	 *
	 * This method will return the entire list of Promotion IDs if <i>$j</i> is not set.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @param int $j [optional] <p>Second list index to retrieve the value from. Defaults to NULL.</p>
	 * @return array|string|boolean array, single value, or <b>FALSE</b> if incorrect index
	 */
	public function getPromotionIds($i = 0, $j = null);

	/**
	 * Returns invoice data for the specified item.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * The array for invoice data may have the following fields:
	 * <ul>
	 * <li><b>InvoiceRequirement</b> - invoice requirement information</li>
	 * <li><b>BuyerSelectedInvoiceCategory</b> - invoice category information selected by the buyer</li>
	 * <li><b>InvoiceTitle</b> - the title of the invoice as specified by the buyer</li>
	 * <li><b>InvoiceInformation</b> - additional invoice information</li>
	 * </ul>
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return array|boolean array, or <b>FALSE</b> if incorrect index
	 */
	public function getInvoiceData($i = 0);

	/**
	 * Returns the condition for the specified item.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * Possible values for the condition ID are...
	 * <ul>
	 * <li>New</li>
	 * <li>Used</li>
	 * <li>Collectible</li>
	 * <li>Refurbished</li>
	 * <li>Preorder</li>
	 * <li>Club</li>
	 * </ul>
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if incorrect index
	 */
	public function getConditionId($i = 0);

	/**
	 * Returns the subcondition for the specified item.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * Possible values for the subcondition ID are...
	 * <ul>
	 * <li>New</li>
	 * <li>Mint</li>
	 * <li>Very Good</li>
	 * <li>Good</li>
	 * <li>Acceptable</li>
	 * <li>Poor</li>
	 * <li>Club</li>
	 * <li>OEM</li>
	 * <li>Warranty</li>
	 * <li>Refurbished Warranty</li>
	 * <li>Refurbished</li>
	 * <li>Open Box</li>
	 * <li>Any</li>
	 * <li>Other</li>
	 * </ul>
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if incorrect index
	 */
	public function getConditionSubtypeId($i = 0);
	/**
	 * Returns the condition description for the specified item.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if incorrect index
	 */
	public function getConditionNote($i = 0);

	/**
	 * Returns the earliest date in the scheduled delivery window for the specified item.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if incorrect index
	 */
	public function getScheduledDeliveryStartDate($i = 0);

	/**
	 * Returns the latest date in the scheduled delivery window for the specified item.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if incorrect index
	 */
	public function getScheduledDeliveryEndDate($i = 0);

	/**
	 * Iterator function
	 * @return type
	 */
	public function current();

	/**
	 * Iterator function
	 */
	public function rewind();

	/**
	 * Iterator function
	 * @return type
	 */
	public function key();

	/**
	 * Iterator function
	 */
	public function next();

	/**
	 * Iterator function
	 * @return type
	 */
	public function valid();
}