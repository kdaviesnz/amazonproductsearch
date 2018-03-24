<?php
/**
 * Created by PhpStorm.
 * User: kevindavies
 * Date: 23/03/18
 * Time: 9:24 AM
 */

namespace kdaviesnz\amazon;


interface IAmazonMWSOrder {

	// These methods are set by the parent class \AmazonOrder

	/**
	 * Fetches items for the order from Amazon.
	 *
	 * See the <i>AmazonOrderItemList</i> class for more information on the returned object.
	 * @param boolean $token [optional] <p>whether or not to automatically use item tokens in the request</p>
	 * @return AmazonOrderItemList container for order's items
	 */
	public function fetchItems($token = false);

	/**
	 * Returns the full set of data for the order.
	 *
	 * This method will return <b>FALSE</b> if the order data has not yet been filled.
	 * The array returned will have the following fields:
	 * <ul>
	 * <li><b>AmazonOrderId</b> - unique ID for the order, which you sent in the first place</li>
	 * <li><b>SellerOrderId</b> (optional) - your unique ID for the order</li>
	 * <li><b>PurchaseDate</b> - time in ISO8601 date format</li>
	 * <li><b>LastUpdateDate</b> - time in ISO8601 date format</li>
	 * <li><b>OrderStatus</b> - the current status of the order, see <i>getOrderStatus</i> for more details</li>
	 * <li><b>MarketplaceId</b> - the marketplace in which the order was placed</li>
	 * <li><b>FulfillmentChannel</b> (optional) - "AFN" or "MFN"</li>
	 * <li><b>SalesChannel</b> (optional) - sales channel for the first item in the order</li>
	 * <li><b>OrderChannel</b> (optional) - order channel for the first item in the order</li>
	 * <li><b>ShipServiceLevel</b> (optional) - shipment service level of the order</li>
	 * <li><b>ShippingAddress</b> (optional) - array, see <i>getShippingAddress</i> for more details</li>
	 * <li><b>OrderTotal</b> (optional) - array with the fields <b>Amount</b> and <b>CurrencyCode</b></li>
	 * <li><b>NumberOfItemsShipped</b> (optional) - number of items shipped</li>
	 * <li><b>NumberOfItemsUnshipped</b> (optional) - number of items not shipped</li>
	 * <li><b>PaymentExecutionDetail</b> (optional) - multi-dimensional array, see <i>getPaymentExecutionDetail</i> for more details</li>
	 * <li><b>PaymentMethod</b> (optional) - "COD", "CVS", or "Other"</li>
	 * <li><b>PaymentMethodDetails</b> (optional) - array of payment detail strings</li>
	 * <li><b>IsReplacementOrder</b> (optional) - "true" or "false"</li>
	 * <li><b>ReplacedOrderId</b> (optional) - Amazon Order ID, only given if <i>IsReplacementOrder</i> is true</li>
	 * <li><b>MarketplaceId</b> (optional) - marketplace for the order</li>
	 * <li><b>BuyerName</b> (optional) - name of the buyer</li>
	 * <li><b>BuyerEmail</b> (optional) - Amazon-generated email for the buyer</li>
	 * <li><b>BuyerCounty</b> (optional) - county for the buyer</li>
	 * <li><b>BuyerTaxInfo</b> (optional) - tax information about the buyer, see <i>getBuyerTaxInfo</i> for more details</li>
	 * <li><b>ShipmentServiceLevelCategory</b> (optional) - "Expedited", "FreeEconomy", "NextDay",
	 * "SameDay", "SecondDay", "Scheduled", or "Standard"</li>
	 * <li><b>ShippedByAmazonTFM</b> (optional) - "true" or "false"</li>
	 * <li><b>TFMShipmentStatus</b> (optional) - the status of the TFM shipment, see <i>getTfmShipmentStatus</i> for more details</li>
	 * <li><b>CbaDisplayableShippingLabel</b> (optional) - customized Checkout by Amazon label of the order</li>
	 * <li><b>OrderType</b> (optional) - "StandardOrder" or "Preorder"</li>
	 * <li><b>EarliestShipDate</b> (optional) - time in ISO8601 date format</li>
	 * <li><b>LatestShipDate</b> (optional) - time in ISO8601 date format</li>
	 * <li><b>EarliestDeliveryDate</b> (optional) - time in ISO8601 date format</li>
	 * <li><b>LatestDeliveryDate</b> (optional) - time in ISO8601 date format</li>
	 * <li><b>IsBusinessOrder</b> (optional) - "true" or "false"</li>
	 * <li><b>PurchaseOrderNumber</b> (optional) - the Purchase Order number entered by the buyer</li>
	 * <li><b>IsPrime</b> (optional) - "true" or "false"</li>
	 * <li><b>IsPremiumOrder</b> (optional) - "true" or "false"</li>
	 * </ul>
	 * @return array|boolean array of data, or <b>FALSE</b> if data not filled yet
	 */
	public function getData();


	/**
	 * Sets the Amazon Order ID. (Required)
	 *
	 * This method sets the Amazon Order ID to be sent in the next request.
	 * This parameter is required for fetching the order from Amazon.
	 * @param string $id <p>either string or number</p>
	 * @return boolean <b>FALSE</b> if improper input
	 */
	public function setOrderId($id);


	/**
	 * Fetches the specified order from Amazon.
	 *
	 * Submits a <i>GetOrder</i> request to Amazon. In order to do this,
	 * an Amazon order ID is required. Amazon will send
	 * the data back as a response, which can be retrieved using <i>getData</i>.
	 * Other methods are available for fetching specific values from the order.
	 * @return boolean <b>FALSE</b> if something goes wrong
	 */
	public function fetchOrder();


	/**
	 * Returns the Amazon Order ID for the Order.
	 *
	 * This method will return <b>FALSE</b> if the order ID has not been set yet.
	 * @return string|boolean single value, or <b>FALSE</b> if order ID not set yet
	 */
	public function getAmazonOrderId();


	/**
	 * Returns the seller-defined ID for the Order.
	 *
	 * This method will return <b>FALSE</b> if the order ID has not been set yet.
	 * @return string|boolean single value, or <b>FALSE</b> if order ID not set yet
	 */
	public function getSellerOrderId();


	/**
	 * Returns the purchase date of the Order.
	 *
	 * This method will return <b>FALSE</b> if the timestamp has not been set yet.
	 * @return string|boolean timestamp, or <b>FALSE</b> if timestamp not set yet
	 */
	public function getPurchaseDate();

	/**
	 * Returns the timestamp of the last modification date.
	 *
	 * This method will return <b>FALSE</b> if the timestamp has not been set yet.
	 * @return string|boolean timestamp, or <b>FALSE</b> if timestamp not set yet
	 */
	public function getLastUpdateDate();


	/**
	 * Returns the status of the Order.
	 *
	 * This method will return <b>FALSE</b> if the order status has not been set yet.
	 * Possible Order Statuses are:
	 * <ul>
	 * <li>Pending</li>
	 * <li>Unshipped</li>
	 * <li>Partially Shipped</li>
	 * <li>Shipped</li>
	 * <li>Cancelled</li>
	 * <li>Unfulfillable</li>
	 * </ul>
	 * @return string|boolean single value, or <b>FALSE</b> if status not set yet
	 */
	public function getOrderStatus();


	/**
	 * Returns the Fulfillment Channel.
	 *
	 * This method will return <b>FALSE</b> if the fulfillment channel has not been set yet.
	 * @return string|boolean "AFN" or "MFN", or <b>FALSE</b> if channel not set yet
	 */
	public function getFulfillmentChannel();


	/**
	 * Returns the Sales Channel of the Order.
	 *
	 * This method will return <b>FALSE</b> if the sales channel has not been set yet.
	 * @return string|boolean single value, or <b>FALSE</b> if channel not set yet
	 */
	public function getSalesChannel();


	/**
	 * Returns the Order Channel of the first item in the Order.
	 *
	 * This method will return <b>FALSE</b> if the order channel has not been set yet.
	 * @return string|boolean single value, or <b>FALSE</b> if channel not set yet
	 */
	public function getOrderChannel();


	/**
	 * Returns the shipment service level of the Order.
	 *
	 * This method will return <b>FALSE</b> if the shipment service level has not been set yet.
	 * @return string|boolean single value, or <b>FALSE</b> if level not set yet
	 */
	public function getShipServiceLevel();


	/**
	 * Returns an array containing all of the address information.
	 *
	 * This method will return <b>FALSE</b> if the address has not been set yet.
	 * The returned array will have the following fields:
	 * <ul>
	 * <li><b>Name</b></li>
	 * <li><b>AddressLine1</b></li>
	 * <li><b>AddressLine2</b></li>
	 * <li><b>AddressLine3</b></li>
	 * <li><b>City</b></li>
	 * <li><b>County</b></li>
	 * <li><b>District</b></li>
	 * <li><b>StateOrRegion</b></li>
	 * <li><b>PostalCode</b></li>
	 * <li><b>CountryCode</b></li>
	 * <li><b>Phone</b></li>
	 * </ul>
	 * @return array|boolean associative array, or <b>FALSE</b> if address not set yet
	 */
	public function getShippingAddress();


	/**
	 * Returns an array containing the total cost of the Order along with the currency used.
	 *
	 * This method will return <b>FALSE</b> if the order total has not been set yet.
	 * The returned array has the following fields:
	 * <ul>
	 * <li><b>Amount</b></li>
	 * <li><b>CurrencyCode</b></li>
	 * </ul>
	 * @return array|boolean associative array, or <b>FALSE</b> if total not set yet
	 */
	public function getOrderTotal();


	/**
	 * Returns just the total cost of the Order.
	 *
	 * This method will return <b>FALSE</b> if the order total has not been set yet.
	 * @return string|boolean number, or <b>FALSE</b> if total not set yet
	 */
	public function getOrderTotalAmount();


	/**
	 * Returns the number of items in the Order that have been shipped.
	 *
	 * This method will return <b>FALSE</b> if the number has not been set yet.
	 * @return integer|boolean non-negative number, or <b>FALSE</b> if number not set yet
	 */
	public function getNumberofItemsShipped();


	/**
	 * Returns the number of items in the Order that have yet to be shipped.
	 *
	 * This method will return <b>FALSE</b> if the number has not been set yet.
	 * @return integer|boolean non-negative number, or <b>FALSE</b> if number not set yet
	 */
	public function getNumberOfItemsUnshipped();


	/**
	 * Returns an array of the complete payment details.
	 *
	 * This method will return <b>FALSE</b> if the payment details has not been set yet.
	 * The array returned contains one or more arrays with the following fields:
	 * <ul>
	 * <li><b>Amount</b></li>
	 * <li><b>CurrencyCode</b></li>
	 * <li><b>SubPaymentMethod</b></li>
	 * </ul>
	 * @return array|boolean multi-dimensional array, or <b>FALSE</b> if details not set yet
	 */
	public function getPaymentExecutionDetail();


	/**
	 * Returns the payment method of the Order.
	 *
	 * This method will return <b>FALSE</b> if the payment method has not been set yet.
	 * @return string|boolean "COD", "CVS", "Other", or <b>FALSE</b> if method not set yet
	 */
	public function getPaymentMethod();


	/**
	 * Returns the payment method details of the Order.
	 *
	 * This method will return <b>FALSE</b> if the payment method details have not been set yet.
	 * @return array|boolean array of detail strings, or <b>FALSE</b> if value not set yet
	 */
	public function getPaymentMethodDetails();


	/**
	 * Returns an indication of whether or not the Order is a Replacement Order.
	 *
	 * This method will return <b>FALSE</b> if the replacement order flag has not been set yet.
	 * @return string|boolean "true" or "false", or <b>FALSE</b> if value not set yet
	 */
	public function getIsReplacementOrder();


	/**
	 * Returns the ID of the Order that this Order replaces.
	 *
	 * This method will return <b>FALSE</b> if the replaced order ID has not been set yet.
	 * @return string|boolean single value, or <b>FALSE</b> if ID not set yet
	 */
	public function getReplacedOrderId();


	/**
	 * Returns the ID of the Marketplace in which the Order was placed.
	 *
	 * This method will return <b>FALSE</b> if the marketplace ID has not been set yet.
	 * @return string|boolean single value, or <b>FALSE</b> if ID not set yet
	 */
	public function getMarketplaceId();


	/**
	 * Returns the name of the buyer.
	 *
	 * This method will return <b>FALSE</b> if the buyer name has not been set yet.
	 * @return string|boolean single value, or <b>FALSE</b> if name not set yet
	 */
	public function getBuyerName();


	/**
	 * Returns the Amazon-generated email address of the buyer.
	 *
	 * This method will return <b>FALSE</b> if the buyer email has not been set yet.
	 * @return string|boolean single value, or <b>FALSE</b> if email not set yet
	 */
	public function getBuyerEmail();


	/**
	 * Returns the county of the buyer.
	 *
	 * This method will return <b>FALSE</b> if the buyer county has not been set yet.
	 * @return string|boolean single value, or <b>FALSE</b> if county not set yet
	 */
	public function getBuyerCounty();


	/**
	 * Returns additional tax information about the buyer.
	 *
	 * This method will return <b>FALSE</b> if the tax info has not been set yet.
	 * The returned array has the following fields:
	 * <ul>
	 * <li><b>CompanyLegalName</b></li>
	 * <li><b>TaxingRegion</b></li>
	 * <li><b>TaxClassifications</b> - array of arrays, each with the following keys:</li>
	 * <ul>
	 * <li><b>Name</b></li>
	 * <li><b>Value</b></li>
	 * </ul>
	 * </ul>
	 * @return array|boolean associative array, or <b>FALSE</b> if info not set yet
	 */
	public function getBuyerTaxInfo();


	/**
	 * Returns the shipment service level category of the Order.
	 *
	 * This method will return <b>FALSE</b> if the service level category has not been set yet.
	 * Valid values for the service level category are...
	 * <ul>
	 * <li>Expedited</li>
	 * <li>FreeEconomy</li>
	 * <li>NextDay</li>
	 * <li>SameDay</li>
	 * <li>SecondDay</li>
	 * <li>Scheduled</li>
	 * <li>Standard</li>
	 * </ul>
	 * @return string|boolean single value, or <b>FALSE</b> if category not set yet
	 */
	public function getShipmentServiceLevelCategory();

	/**
	 * Use getShipmentServiceLevelCategory instead.
	 * @deprecated since version 1.3.0
	 * @return string|boolean single value, or <b>FALSE</b> if category not set yet
	 */
	public function getShipServiceLevelCategory();


	/**
	 * Returns the customized Checkout by Amazon (CBA) label of the Order.
	 *
	 * This method will return <b>FALSE</b> if the CBA label category has not been set yet.
	 * @return string|boolean single value, or <b>FALSE</b> if label not set yet
	 */
	public function getCbaDisplayableShippingLabel();
	/**
	 * Returns an indication of whether or not the Order was shipped with the Amazon TFM service.
	 *
	 * This method will return <b>FALSE</b> if the Amazon TFM flag has not been set yet.
	 * @return string|boolean single value, or <b>FALSE</b> if value not set yet
	 */
	public function getShippedByAmazonTfm();

	/**
	 * Returns the status of an Order shipped using Amazon TFM.
	 *
	 * This method will return <b>FALSE</b> if the status has not been set yet.
	 * Valid values for the status are...
	 * <ul>
	 * <li>PendingPickUp</li>
	 * <li>LabelCanceled</li>
	 * <li>PickedUp</li>
	 * <li>AtDestinationFC</li>
	 * <li>Delivered</li>
	 * <li>RejectedByBuyer</li>
	 * <li>Undeliverable</li>
	 * <li>ReturnedToSeller</li>
	 * </ul>
	 * @return string|boolean single value, or <b>FALSE</b> if status not set yet
	 */
	public function getTfmShipmentStatus();
	/**
	 * Returns the type of the order.
	 *
	 * This method will return <b>FALSE</b> if the type has not been set yet.
	 * Valid values for the type are...
	 * <ul>
	 * <li>StandardOrder</li>
	 * <li>Preorder</li>
	 * </ul>
	 * @return string|boolean single value, or <b>FALSE</b> if order type not set yet
	 */
	public function getOrderType();

	/**
	 * Returns the timestamp of the earliest shipping date.
	 *
	 * This method will return <b>FALSE</b> if the timestamp has not been set yet.
	 * @return string|boolean timestamp, or <b>FALSE</b> if timestamp not set yet
	 */
	public function getEarliestShipDate();

	/**
	 * Returns the timestamp of the latest shipping date.
	 *
	 * Note that this could be set to midnight of the day after the last date,
	 * so the timestamp "2013-09-025T00:00:00Z" indicates the last day is the 24th and not the 25th.
	 * This method will return <b>FALSE</b> if the timestamp has not been set yet.
	 * @return string|boolean timestamp, or <b>FALSE</b> if timestamp not set yet
	 */
	public function getLatestShipDate();

	/**
	 * Returns the timestamp of the estimated earliest delivery date.
	 *
	 * This method will return <b>FALSE</b> if the timestamp has not been set yet.
	 * @return string|boolean timestamp, or <b>FALSE</b> if timestamp not set yet
	 */
	public function getEarliestDeliveryDate();


	/**
	 * Returns the timestamp of the estimated latest delivery date.
	 *
	 * Note that this could be set to midnight of the day after the last date,
	 * so the timestamp "2013-09-025T00:00:00Z" indicates the last day is the 24th and not the 25th.
	 * This method will return <b>FALSE</b> if the timestamp has not been set yet.
	 * @return string|boolean timestamp, or <b>FALSE</b> if timestamp not set yet
	 */
	public function getLatestDeliveryDate();

	/**
	 * Returns the ratio of shipped items to unshipped items.
	 *
	 * This method will return <b>FALSE</b> if the shipment numbers have not been set yet.
	 * @return float|boolean Decimal number from 0 to 1, or <b>FALSE</b> if numbers not set yet
	 */
	public function getPercentShipped();


	/**
	 * Returns an indication of whether or not the Order is a business number.
	 *
	 * This method will return <b>FALSE</b> if the business order flag has not been set yet.
	 * @return string|boolean "true" or "false", or <b>FALSE</b> if value not set yet
	 */
	public function getIsBusinessOrder();

	/**
	 * Returns the purchase order number associated with the order.
	 *
	 * This method will return <b>FALSE</b> if the purchase order number has not been set yet.
	 * @return string|boolean single value, or <b>FALSE</b> if value not set yet
	 */
	public function getPurchaseOrderNumber();

	/**
	 * Returns an indication of whether or not the Order uses the Amazon Prime service.
	 *
	 * This method will return <b>FALSE</b> if the Prime flag has not been set yet.
	 * @return string|boolean "true" or "false", or <b>FALSE</b> if value not set yet
	 */
	public function getIsPrime();


	/**
	 * Returns an indication of whether or not the Order is a premium order.
	 *
	 * This method will return <b>FALSE</b> if the premium order flag has not been set yet.
	 * @return string|boolean single value, or <b>FALSE</b> if value not set yet
	 */
	public function getIsPremiumOrder();








}