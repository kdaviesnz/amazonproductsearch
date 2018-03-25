<?php

namespace kdaviesnz\amazon;

use ApaiIO\ApaiIO;
use ApaiIO\Operations\RelatedProductsLookup;
use ApaiIO\Operations\SimilarityLookup;
use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\Operations\Lookup;

/**
 * Class AmazonProduct
 * @package kdaviesnz\amazon
 */
class AmazonProduct implements IAmazonProduct
{
    private $id = null;
    private $keyType = "";
    private $keyValue = "";
    private $asin = "";
    private $detailPageURL = "";
    private $itemLinks = array();
    private $salesRank = 0;
    private $author = "";
    private $type = "";
    private $brand = "";
    private $department = "";
    private $color = "";
    private $EAN = "";
    private $feature = array();
    private $genre = "";
    private $isAdultProduct = false; // bool
    private $isAutographed = false; // bool
    private $isMemorabilia = false; // bool
    private $itemDimensions = array();
    private $label = "";
    private $languages = array(); // array
    private $listPriceAmount = "";
    private $listPriceCurrencyCode = "";
    private $listPriceFormattedPrice = "";
    private $manufacturer = "";
    private $productGroup = "";
    private $productTypeName = "";
    private $publisher = "";
    private $title = "";
    private $lowestNewPriceAmount = "";
    private $lowestNewPriceCurrencyCode = "";
    private $lowestNewPriceFormattedPrice = "";
    private $LowestUsedPriceAmount = "";
    private $lowestUsedPriceCurrencyCode = "";
    private $lowestUsedPriceFormattedPrice = "";
    private $lowestCollectiblePriceAmount = "";
    private $lowestCollectiblePriceCurrencyCode = "";
    private $lowestCollectiblePriceFormattedPrice = "";
    private $related_products = array();
    private $frequently_bought_together = array();
    private $similar_products = array();
    private $images = array();
    private $categories = array();
    private $rrfs = array();
    private $mpn = '';
    private $merchant = '';
    private $warranty = '';
    private $image_sets = '';
    private $amountSaved = 0.00;
    private $availablity = '';
    private $freeShippingMessage = '';
    private $customerReview = '';
    private $editorialReview = '';

    private $t30days = null;
	private $t6months = null;
	private $t12months = null;
	private $t30daysSalesCount = null;
	private $t6monthsSalesCount = null;
	private $t12monthsSalesCount = null;

    /**
     * AmazonProduct constructor.
     */
    public function __construct(
         $id,
         $keyType,
         $keyValue,
         $asin,//1
         $detailPageURL,
         $itemLinks,
         $salesRank,
         $author,
         $type,
         $brand,
         $department,
         $color,
         $EAN,//10
         $feature,
         $genre,
         $isAdultProduct,
         $isAutographed,
         $isMemorabilia,
         $itemDimensions,
         $label,
         $languages, // 18
         $listPriceAmount,
         $listPriceCurrencyCode, //20
         $listPriceFormattedPrice,
         $manufacturer,
         $productGroup,
         $productTypeName,
         $publisher,
         $title,
         $lowestNewPriceAmount,
         $lowestNewPriceCurrencyCode,
         $lowestNewPriceFormattedPrice,
         $LowestUsedPriceAmount, // 30
         $lowestUsedPriceCurrencyCode,
         $lowestUsedPriceFormattedPrice,
         $lowestCollectiblePriceAmount,
         $lowestCollectiblePriceCurrencyCode,
         $lowestCollectiblePriceFormattedPrice,
         $images,
         $similar_products,
         $categories,
         $rrfs, // 39
         $mpn,
         $merchant,
         $warranty,
         $image_sets,
         $amountSaved,
         $availability,
         $freeShippingMessage,
         $customerReview,
        $editorialReview
    ){

        $this->id = $id;
        $this->keyValue = $keyValue;
        $this->keyType = $keyType;
        $this->asin = $asin;
        $this->detailPageURL = $detailPageURL;
        $this->itemLinks = $itemLinks;
        $this->salesRank = $salesRank;
        $this->author = $author;
        $this->type = $type;
        $this->brand = $brand;
        $this->department = $department;
        $this->color = $color;
        $this->EAN = $EAN;
        $this->feature = $feature;
        $this->genre = $genre;
        $this->isAdultProduct = $isAdultProduct;
        $this->isAutographed = $isAutographed;
        $this->isMemorabilia = $isMemorabilia;
        $this->itemDimensions = $itemDimensions;
        $this->label = $label;
        $this->languages = $languages;
        $this->listPriceAmount = $listPriceAmount;
        $this->listPriceCurrencyCode = $listPriceCurrencyCode;
        $this->listPriceFormattedPrice = $listPriceFormattedPrice;
        $this->manufacturer = $manufacturer;
        $this->productGroup = $productGroup;
        $this->productTypeName = $productTypeName;
        $this->publisher = $publisher;
        $this->title = $title;
        $this->lowestNewPriceAmount = $lowestNewPriceAmount;
        $this->lowestNewPriceCurrencyCode = $lowestNewPriceCurrencyCode;
        $this->lowestNewPriceFormattedPrice = $lowestNewPriceFormattedPrice;
        $this->LowestUsedPriceAmount = $LowestUsedPriceAmount;
        $this->lowestUsedPriceCurrencyCode = $lowestUsedPriceCurrencyCode;
        $this->lowestUsedPriceFormattedPrice = $lowestUsedPriceFormattedPrice;
        $this->lowestCollectiblePriceAmount = $lowestCollectiblePriceAmount;
        $this->lowestCollectiblePriceCurrencyCode = $lowestCollectiblePriceCurrencyCode;
        $this->lowestCollectiblePriceFormattedPrice = $lowestCollectiblePriceFormattedPrice;
        $this->images = $images;
        $this->similar_products = $similar_products;

        $this->categories = $categories;
        $this->rrfs = $rrfs;
        $this->mpn = $mpn;
        $this->merchant = $merchant;
        $this->warranty = $warranty;
        $this->image_sets = $image_sets;
        $this->amountSaved = $amountSaved;
        $this->availablity = $availability;
        $this->freeShippingMessage = $freeShippingMessage;
        $this->customerReview = $customerReview;
        $this->editorialReview = $editorialReview;
    }

	public function getId():String {
        return $this->id;
    }

    public function getKeyType() {
        return $this->keyType;
    }

	public function getKeyValue() {
		return $this->keyValue;
	}

	public function getDescription():String {
        return "";
    }

	public function getUPCEAN():String {
        return "";
    }

    public function getCompetitivePrice():float {
        return 0.00;
    }

	public function getNumberOfCompetitiveSellers():int {
        return 0;
    }

	public function get30Days():float {
        return $this->t30days;
    }

	public function get6Months():float {
		return $this->t6months;
    }

	public function get12months():float {
        return $this->t12months;
    }

	public function get30DaysSalesCount():float {
		return $this->t30daysSalesCount;
	}

	public function get6MonthsSalesCount():float {
		return $this->t6monthsSalesCount;;
	}

	public function get12monthsSalesCount():float {
		return $this->t12monthsSalesCount;;
	}

	public function getCost():float {
        return 0.00;
    }

	public function getSoldByAmazon():bool {
        return true;
    }

	public function getMarginPerc():float {
        return 0.00;
    }

	public function getMarginAmt():float {
        return 0.00;
    }

    public function getImageSets() {
        return $this->image_sets;
    }

    public function getWarranty() {
        return $this->warranty;
    }

    public function getAvgRRF() {
        $rrfs = $this->getRRFs(); // key is category id, value is rff value;
        return count( $rrfs ) > 0 ? array_sum( $rrfs) / count( $rrfs ) : 0.00;
    }

    /**
     * @return array
     */
    public function getCategories() {
        return $this->categories;
    }


    /**
     * @return array
     */
    public function getImages()
    {
        return $this->images;
    }


    /**
     * @return string
     */
    public function getAsin()
    {
        return $this->asin;
    }

    /**
     * @return string
     */
    public function getDetailPageURL()
    {
        return $this->detailPageURL;
    }

    /**
     * @return array
     */
    public function getItemLinks()
    {
        return $this->itemLinks;
    }

    /**
     * @return string
     */
    public function getSalesRank()
    {
        return $this->salesRank;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @return string
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @return string
     */
    public function getEAN()
    {
        return $this->EAN;
    }

    /**
     * @return array
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * @return string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * @return boolean
     */
    public function isIsAdultProduct()
    {
        return $this->isAdultProduct;
    }

    /**
     * @return boolean
     */
    public function isIsAutographed()
    {
        return $this->isAutographed;
    }

    /**
     * @return boolean
     */
    public function isIsMemorabilia()
    {
        return $this->isMemorabilia;
    }

    /**
     * @return array
     */
    public function getItemDimensions()
    {
        return $this->itemDimensions;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @return string
     */
    public function getListPriceAmount()
    {
        return $this->listPriceAmount;
    }

    /**
     * @return string
     */
    public function getListPriceCurrencyCode()
    {
        return $this->listPriceCurrencyCode;
    }

    /**
     * @return string
     */
    public function getListPriceFormattedPrice()
    {
        return $this->listPriceFormattedPrice;
    }

    /**
     * @return string
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * @return string
     */
    public function getProductGroup()
    {
        return $this->productGroup;
    }

    /**
     * @return string
     */
    public function getProductTypeName()
    {
        return $this->productTypeName;
    }

    /**
     * @return string
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getLowestNewPriceAmount()
    {
        return $this->lowestNewPriceAmount;
    }

    /**
     * @return string
     */
    public function getLowestNewPriceCurrencyCode()
    {
        return $this->lowestNewPriceCurrencyCode;
    }

    /**
     * @return string
     */
    public function getLowestNewPriceFormattedPrice()
    {
        return $this->lowestNewPriceFormattedPrice;
    }

    /**
     * @return string
     */
    public function getLowestUsedPriceAmount()
    {
        return $this->LowestUsedPriceAmount;
    }

    /**
     * @return string
     */
    public function getLowestUsedPriceCurrencyCode()
    {
        return $this->lowestUsedPriceCurrencyCode;
    }

    /**
     * @return string
     */
    public function getLowestUsedPriceFormattedPrice()
    {
        return $this->lowestUsedPriceFormattedPrice;
    }

    /**
     * @return string
     */
    public function getLowestCollectiblePriceAmount()
    {
        return $this->lowestCollectiblePriceAmount;
    }

    /**
     * @return string
     */
    public function getLowestCollectiblePriceCurrencyCode()
    {
        return $this->lowestCollectiblePriceCurrencyCode;
    }

    /**
     * @return string
     */
    public function getLowestCollectiblePriceFormattedPrice()
    {
        return $this->lowestCollectiblePriceFormattedPrice;
    }


    /**
     * @return array
     */
    public function related_products( $relationshipType ) {

        // See http://docs.aws.amazon.com/AWSECommerceService/latest/DG/Motivating_RelatedItems.html#RelationshipTypes
        // for full list of relationship types.
        // ref http://docs.aws.amazon.com/AWSECommerceService/latest/DG/EX_FindingRelatedItems.html
        $conf = new GenericConfiguration();
        $client = new \GuzzleHttp\Client();
        $request = new \ApaiIO\Request\GuzzleRequest($client);

        $amazon_accounts = AmazonSettings::amazon_accounts()->value();
        $aws_access_key_id = $amazon_accounts[0]['amazon_access_key_id'];
        $aws_secret_key = $amazon_accounts[0]['amazon_secret_access_key'];
        $affiliate_tag = $amazon_accounts[0]['amazon_affiliate_link'];

        $conf
            ->setCountry('com')
            ->setAccessKey( $aws_access_key_id )
            ->setSecretKey( $aws_secret_key )
            ->setAssociateTag( $affiliate_tag )
            ->setRequest($request);
        $apaiIo = new ApaiIO( $conf );

      //  $relatedProductsLookup = new RelatedProductsLookup( $relationshipType );
        /*
        $relatedProductsLookup = new RelatedProductsLookup($relationshipType);

        $relatedProductsLookup->setResponseGroup(array('Large', 'Accessories', 'BrowseNodes', 'Images', 'ItemAttributes', 'SalesRank', 'Similarities', 'Variations', 'SalesRank', 'EditorialReview'));
        $relatedProductsLookup->setItemId( $this->asin );

        $search_results_xml = $apaiIo->runOperation( $relatedProductsLookup );

        // Parse results.
        $parser = new AmazonParser();
        $results = $parser->parse_related_items_search_results( $search_results_xml );


        return $results['items'];
        */
        return array();

    }


    /**
     * This gets products that are frequently bought together with the current product.
     * @return array
     */
    public function getSimilarProducts()
    {
        return $this->similar_products;
    }

    /**
     * @return array
     */
    public function frequently_bought_together() {

        if ( ! empty( $this->similar_products ) ) {
            return $this->similar_products;
        }

        // ref http://docs.pixel-web.org/apai-io/master/chapters/built-in-operations.html#similaritylookup.
        $conf = new GenericConfiguration();
        $client = new \GuzzleHttp\Client();
        $request = new \ApaiIO\Request\GuzzleRequest($client);

        $amazon_accounts = AmazonSettings::amazon_accounts()->value();
        $aws_access_key_id = $amazon_accounts[0]['amazon_access_key_id'];
        $aws_secret_key = $amazon_accounts[0]['amazon_secret_access_key'];
        $affiliate_tag = $amazon_accounts[0]['amazon_affiliate_link'];

        $conf
            ->setCountry('com')
            ->setAccessKey( $aws_access_key_id )
            ->setSecretKey( $aws_secret_key )
            ->setAssociateTag( $affiliate_tag )
            ->setRequest($request);

        $apaiIo = new ApaiIO( $conf );
        $similaritylookup = new SimilarityLookup();
        $similaritylookup->setResponseGroup(array(array('Large', 'Accessories', 'BrowseNodes', 'Images', 'ItemAttributes', 'SalesRank', 'Similarities', 'Variations', 'SalesRank','EditorialReview')));

        $similaritylookup->setItemId( $this->asin );

        $search_results_xml = $apaiIo->runOperation($similaritylookup);

        // Parse results.
        $parser = new AmazonParser();
        $results = $parser->parse_frequently_bought_together_search_results( $search_results_xml );

        return $results['items'];

    }

    /**
     * @return array
     */
    public function getRRFs()
    {
        // @todo
       // $data = get_transient('productrrfs' . $this->getAsin());
        $data = false;

        if ($data == false) {

            $rrfs = array();
            $categories = $this->getCategories();
            $salesRank = $this->getSalesRank();
            foreach ($categories as $category) {
                $rrfs[$category->get_category_id()] = (float)$salesRank / (float)$category->get_number_of_items();
                $ancestors = $category->get_ancestor_categories();
                $rrfs = AmazonCache::get_ancestor_rrfs($rrfs, $ancestors, $salesRank, 1);
            }

            // @todo
//            set_transient('productrrfs' . $this->getAsin(), $data, 3600 * 24 * 1);

        }

        return $rrfs;
    }

    /**
     * @return string
     */
    public function getMerchant()
    {
        return $this->merchant;
    }

    /**
 * @return string
 */
    public function getMpn()
    {
        return $this->mpn;
    }

    /**
     * @return float
     */
    public function getAmountSaved() {
        return $this->amountSaved;
    }

    /**
     * @return string
     */
    public function getAvailability() {
        return $this->availablity;
    }

    /**
     * @return string
     */
    public function getFreeShippingMessage() {
        return $this->freeShippingMessage;
    }

    /**
     * @return string
     */
    public function getCustomerReview() {
        $review = $this->customerReview;
        $links = $this->getItemLinks();
        if ( empty( $review ) && isset($links[5])) {
            $review = $links[5];
        }
        return $review;
    }

    public function getCustomerReviewIFrame() {
        ob_start();
        ?>
        <iframe src="<?php echo $this->getCustomerReview(); ?>"></iframe>
<?php
        return ob_get_clean();
    }

    /**
     * @return string
     */
    public function getEditorialReview() {
        return $this->editorialReview;
    }

	public function addSalesData(array $salesData):bool {

        global $conn;
        $amazondb = new AmazonDB($conn);

        $T30daysSafe = mysqli_real_escape_string($conn, $salesData["30days"]);
		$T6monthsSafe = mysqli_real_escape_string($conn, $salesData["6months"]);
		$T12monthsSafe = mysqli_real_escape_string($conn, $salesData["12months"]);
		$T30daysSalesCountSafe = mysqli_real_escape_string($conn, $salesData["30daysSalesCount"]);
		$T6monthsSalesCountSafe = mysqli_real_escape_string($conn, $salesData["6monthsSalesCount"]);
		$T12monthsSalesCountSafe = mysqli_real_escape_string($conn, $salesData["12monthsSalesCount"]);

		$keyTypeSafe = mysqli_real_escape_string($conn, $this->keyType);
		$keyValueSafe = mysqli_real_escape_string($conn, $this->keyValue);

		$sql = "UPDATE `wp_amazon_amazon_products` SET 
                  `T30days` = '$T30daysSafe',
                  `T6months` = '$T6monthsSafe',
                  `T12months` = '$T12monthsSafe',
                  `T30daysSalesCount` = '$T30daysSalesCountSafe',
                  `T6monthsSalesCount` = '$T6monthsSalesCountSafe',
                  `T12monthsSalesCount` = '$T12monthsSalesCountSafe'
                  WHERE `keyValue` = '$keyValueSafe' AND `keyType`='$keyTypeSafe'";
		$amazondb->query($sql);

		$this->t30days = $salesData["30days"];
		$this->t6months = $salesData["6months"];
		$this->t12months = $salesData["12months"];
		$this->t30daysSalesCount = $salesData["30daysSalesCount"];
		$this->t6monthsSalesCount = $salesData["6monthsSalesCount"];
		$this->t12monthsSalesCount = $salesData["12monthsSalesCount"];

		return true;

    }


}