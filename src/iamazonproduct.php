<?php

namespace kdaviesnz\amazon;

use ApaiIO\ApaiIO;


interface IAmazonProduct
{
    public function getAsin();
    public function getDetailPageURL();
    public function getItemLinks();
    public function getSalesRank();
    public function getAuthor();
    public function getType();
    public function getBrand();
    public function getDepartment();
    public function getColor();
    public function getEAN();
    public function getFeature();
    public function getGenre();
    public function isIsAdultProduct();
    public function isIsAutographed();
    public function isIsMemorabilia();
    public function getItemDimensions();
    public function getLabel();
    public function getLanguages();
    public function getListPriceAmount();
    public function getListPriceCurrencyCode();
    public function getListPriceFormattedPrice();
    public function getManufacturer();
    public function getProductGroup();
    public function getProductTypeName();
    public function getPublisher();
    public function getTitle();
    public function getLowestNewPriceAmount();
    public function getLowestNewPriceCurrencyCode();
    public function getLowestNewPriceFormattedPrice();
    public function getLowestUsedPriceAmount();
    public function getLowestUsedPriceCurrencyCode();
    public function getLowestUsedPriceFormattedPrice();
    public function getLowestCollectiblePriceAmount();
    public function getLowestCollectiblePriceCurrencyCode();
    public function getRRFs();
    public function getImages();
    public function getCategories();
    public function getSimilarProducts();
    public function getAvgRRF();
    public function getMPN();
    public function getMerchant(); // see http://stackoverflow.com/questions/8282392/how-to-get-seller-name-from-amazon-in-itemsearch-using-amazon-api
  //  public function add_to_database();

    public function frequently_bought_together();
    public function related_products( $relationshipType );

    public function getWarranty();
    public function getImageSets();
    
    public function getAmountSaved();
    public function getAvailability();
    public function getFreeShippingMessage();
    public function getCustomerReview();
    public function getEditorialReview();
    public function getCustomerReviewIFrame();

}