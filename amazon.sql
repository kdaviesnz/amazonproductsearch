-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 02, 2018 at 09:26 AM
-- Server version: 5.6.38
-- PHP Version: 7.2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_amazon_categories`
--

CREATE TABLE `wp_amazon_amazon_categories` (
  `category_id` varchar(10) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `number_items` int(11) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_amazon_products`
--

CREATE TABLE `wp_amazon_amazon_products` (
  `id` bigint(20) NOT NULL,
  `keyType` varchar(10) NOT NULL,
  `keyValue` varchar(100) NOT NULL,
  `AIN` varchar(20) NOT NULL,
  `title` varchar(100) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `detailPageURL` varchar(200) NOT NULL,
  `salesRank` int(11) NOT NULL,
  `author` varchar(50) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `department` varchar(50) NOT NULL,
  `color` varchar(10) NOT NULL,
  `ean` varchar(20) NOT NULL,
  `feature` varchar(200) NOT NULL,
  `genre` varchar(10) NOT NULL,
  `isAdultProduct` tinyint(11) NOT NULL,
  `isAutographed` tinyint(11) NOT NULL,
  `isMemorabilia` tinyint(11) NOT NULL,
  `label` varchar(50) NOT NULL,
  `publisher` varchar(50) NOT NULL,
  `listPriceAmount` float NOT NULL,
  `listPriceCurrencyCode` varchar(10) NOT NULL,
  `listPriceFormattedPrice` varchar(20) NOT NULL,
  `manufacturer` varchar(50) NOT NULL,
  `productGroup` varchar(50) NOT NULL,
  `productTypeName` varchar(50) NOT NULL,
  `lowestNewPriceAmount` float NOT NULL,
  `lowestNewPriceCurrencyCode` varchar(10) NOT NULL,
  `lowestNewPriceFormattedPrice` varchar(10) NOT NULL,
  `lowestUsedPriceAmount` float NOT NULL,
  `lowestUsedPriceCurrencyCode` varchar(10) NOT NULL,
  `lowestUsedPriceFormattedPrice` varchar(10) NOT NULL,
  `lowestCollectiblePriceAmount` float NOT NULL,
  `lowestCollectiblePriceCurrencyCode` varchar(10) NOT NULL,
  `lowestCollectiblePriceFormattedPrice` varchar(10) NOT NULL,
  `binding` varchar(50) NOT NULL,
  `avgRRF` float NOT NULL,
  `mpn` varchar(40) NOT NULL,
  `merchant` varchar(100) NOT NULL,
  `warranty` text NOT NULL,
  `amountSaved` float NOT NULL,
  `availability` text NOT NULL,
  `freeShippingMessage` text NOT NULL,
  `customerReview` varchar(200) NOT NULL,
  `editorialReview` varchar(200) NOT NULL,
  `T30days` float DEFAULT NULL,
  `T6months` float DEFAULT NULL,
  `T12months` float DEFAULT NULL,
  `T30daysSalesCount` int(11) DEFAULT NULL,
  `T6monthsSalesCount` int(11) DEFAULT NULL,
  `T12monthsSalesCount` int(11) DEFAULT NULL,
  `soldByAmazon` tinyint(4) DEFAULT NULL,
  `competitivePrice` float DEFAULT NULL,
  `cost` float DEFAULT NULL,
  `UPC` varchar(50) DEFAULT NULL,
  `numberOfCompetitiveSellers` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_amazon_product_visits`
--

CREATE TABLE `wp_amazon_amazon_product_visits` (
  `keyValue` varchar(40) NOT NULL,
  `url` varchar(200) NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `amazon_country` varchar(100) NOT NULL,
  `user_IP` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_categories_categories`
--

CREATE TABLE `wp_amazon_categories_categories` (
  `categoryID` varchar(10) NOT NULL,
  `parentCategoryID` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_geo`
--

CREATE TABLE `wp_amazon_geo` (
  `ip` varchar(50) NOT NULL,
  `country` varchar(100) NOT NULL,
  `countryCode` varchar(10) NOT NULL,
  `region` varchar(50) NOT NULL,
  `city` varchar(200) NOT NULL,
  `areaCode` varchar(10) NOT NULL,
  `continentCode` varchar(10) NOT NULL,
  `latitude` float NOT NULL,
  `longitude` float NOT NULL,
  `currencyCode` varchar(5) NOT NULL,
  `currencySymbol` varchar(5) NOT NULL,
  `currencySymbolUtf8` varchar(10) NOT NULL,
  `currencyConverter` float NOT NULL,
  `amazonCountryExt` varchar(5) NOT NULL,
  `regionCode` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_item_dimensions`
--

CREATE TABLE `wp_amazon_item_dimensions` (
  `keyValue` varchar(20) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `depth` int(11) NOT NULL,
  `weight` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_keywords`
--

CREATE TABLE `wp_amazon_keywords` (
  `phrase` varchar(50) NOT NULL,
  `variation` varchar(50) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_keywords_categories`
--

CREATE TABLE `wp_amazon_keywords_categories` (
  `phrase` varchar(50) NOT NULL,
  `category_name` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_options`
--

CREATE TABLE `wp_amazon_options` (
  `option_id` bigint(20) UNSIGNED NOT NULL,
  `option_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `autoload` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_post_amazon_products`
--

CREATE TABLE `wp_amazon_post_amazon_products` (
  `keyValue` varchar(20) NOT NULL,
  `post_id` bigint(20) NOT NULL,
  `campaignname` varchar(50) NOT NULL,
  `type` varchar(1) NOT NULL DEFAULT 'A',
  `use_product` tinyint(4) NOT NULL DEFAULT '1',
  `products_rating` tinyint(4) NOT NULL,
  `add_to_cart` tinyint(4) NOT NULL DEFAULT '0',
  `product_popup` tinyint(4) NOT NULL DEFAULT '0',
  `product_offer_url` varchar(200) NOT NULL,
  `product_small_img` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_product_categories`
--

CREATE TABLE `wp_amazon_product_categories` (
  `productID` int(20) NOT NULL,
  `searchPhrase` varchar(50) NOT NULL,
  `searchIndex` varchar(50) NOT NULL,
  `RRF` float NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_product_features`
--

CREATE TABLE `wp_amazon_product_features` (
  `keyValue` varchar(20) NOT NULL,
  `feature` varchar(200) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_product_frequently_bought_together`
--

CREATE TABLE `wp_amazon_product_frequently_bought_together` (
  `keyValue` varchar(20) NOT NULL,
  `frequentlyBoughtTogether` varchar(20) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_product_images`
--

CREATE TABLE `wp_amazon_product_images` (
  `keyValue` varchar(20) NOT NULL,
  `src` varchar(200) NOT NULL,
  `height` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_product_image_sets`
--

CREATE TABLE `wp_amazon_product_image_sets` (
  `keyValue` varchar(20) NOT NULL,
  `type` varchar(10) NOT NULL,
  `height` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `url` varchar(200) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_product_languages`
--

CREATE TABLE `wp_amazon_product_languages` (
  `keyValue` varchar(20) NOT NULL,
  `language` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_product_links`
--

CREATE TABLE `wp_amazon_product_links` (
  `keyValue` varchar(20) NOT NULL,
  `link` varchar(200) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_product_related_products`
--

CREATE TABLE `wp_amazon_product_related_products` (
  `keyValue` varchar(20) NOT NULL,
  `relatedProduct` varchar(20) NOT NULL,
  `relationshipType` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_related_products`
--

CREATE TABLE `wp_amazon_related_products` (
  `keyValue` varchar(20) NOT NULL,
  `related` varchar(20) NOT NULL,
  `lastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `relationshipType` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wp_amazon_search`
--

CREATE TABLE `wp_amazon_search` (
  `searchTerm` varchar(200) NOT NULL,
  `keyValue` varchar(20) NOT NULL,
  `type` varchar(10) NOT NULL,
  `page` int(11) NOT NULL DEFAULT '1',
  `searchType` varchar(50) NOT NULL,
  `search_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wp_amazon_amazon_categories`
--
ALTER TABLE `wp_amazon_amazon_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `wp_amazon_amazon_products`
--
ALTER TABLE `wp_amazon_amazon_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wp_amazon_amazon_product_visits`
--
ALTER TABLE `wp_amazon_amazon_product_visits`
  ADD PRIMARY KEY (`keyValue`,`url`,`time`,`amazon_country`,`user_IP`);

--
-- Indexes for table `wp_amazon_categories_categories`
--
ALTER TABLE `wp_amazon_categories_categories`
  ADD PRIMARY KEY (`categoryID`,`parentCategoryID`);

--
-- Indexes for table `wp_amazon_geo`
--
ALTER TABLE `wp_amazon_geo`
  ADD PRIMARY KEY (`ip`);

--
-- Indexes for table `wp_amazon_item_dimensions`
--
ALTER TABLE `wp_amazon_item_dimensions`
  ADD PRIMARY KEY (`keyValue`);

--
-- Indexes for table `wp_amazon_keywords`
--
ALTER TABLE `wp_amazon_keywords`
  ADD PRIMARY KEY (`phrase`,`variation`);

--
-- Indexes for table `wp_amazon_keywords_categories`
--
ALTER TABLE `wp_amazon_keywords_categories`
  ADD PRIMARY KEY (`phrase`,`category_name`);

--
-- Indexes for table `wp_amazon_options`
--
ALTER TABLE `wp_amazon_options`
  ADD PRIMARY KEY (`option_id`),
  ADD UNIQUE KEY `option_name` (`option_name`);

--
-- Indexes for table `wp_amazon_post_amazon_products`
--
ALTER TABLE `wp_amazon_post_amazon_products`
  ADD PRIMARY KEY (`keyValue`,`post_id`,`campaignname`);

--
-- Indexes for table `wp_amazon_product_categories`
--
ALTER TABLE `wp_amazon_product_categories`
  ADD PRIMARY KEY (`productID`,`searchPhrase`,`searchIndex`);

--
-- Indexes for table `wp_amazon_product_features`
--
ALTER TABLE `wp_amazon_product_features`
  ADD PRIMARY KEY (`keyValue`,`feature`);

--
-- Indexes for table `wp_amazon_product_frequently_bought_together`
--
ALTER TABLE `wp_amazon_product_frequently_bought_together`
  ADD PRIMARY KEY (`keyValue`,`frequentlyBoughtTogether`);

--
-- Indexes for table `wp_amazon_product_images`
--
ALTER TABLE `wp_amazon_product_images`
  ADD PRIMARY KEY (`keyValue`,`src`);

--
-- Indexes for table `wp_amazon_product_image_sets`
--
ALTER TABLE `wp_amazon_product_image_sets`
  ADD PRIMARY KEY (`keyValue`,`type`);

--
-- Indexes for table `wp_amazon_product_languages`
--
ALTER TABLE `wp_amazon_product_languages`
  ADD PRIMARY KEY (`keyValue`,`language`);

--
-- Indexes for table `wp_amazon_product_links`
--
ALTER TABLE `wp_amazon_product_links`
  ADD PRIMARY KEY (`keyValue`,`link`);

--
-- Indexes for table `wp_amazon_product_related_products`
--
ALTER TABLE `wp_amazon_product_related_products`
  ADD PRIMARY KEY (`keyValue`,`relatedProduct`,`relationshipType`);

--
-- Indexes for table `wp_amazon_related_products`
--
ALTER TABLE `wp_amazon_related_products`
  ADD UNIQUE KEY `AIN` (`keyValue`,`related`,`relationshipType`);

--
-- Indexes for table `wp_amazon_search`
--
ALTER TABLE `wp_amazon_search`
  ADD PRIMARY KEY (`searchTerm`,`keyValue`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wp_amazon_amazon_products`
--
ALTER TABLE `wp_amazon_amazon_products`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `wp_amazon_options`
--
ALTER TABLE `wp_amazon_options`
  MODIFY `option_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=487;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
