<?php
/**
 * Created by PhpStorm.
 * User: kevindavies
 * Date: 23/03/18
 * Time: 12:12 PM
 */

namespace kdaviesnz\amazon;


class AmazonMWSTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {

	}

	public function tearDown() {

	}

	public function testMinimumViableTest() {
		/** @noinspection PhpUndefinedMethodInspection */
		$this->assertTrue( true, "true didn't end up being false!" );
	}


	public function testGenerateEstimatedSalesReport() {

		include("vendor/autoload.php");
		include("src/config.php");
		include("src/iamazonproduct.php");
		include("src/amazonproduct.php");
		include("src/iamazoncategory.php");
		include("src/amazoncategory.php");
		include("src/isetting.php");
		include("src/setting.php");
		include("src/ioption.php");
		include("src/option.php");
		include("src/isettings.php");
		include("src/amazonsettings.php");
		include("src/iamazoncache.php");
		include("src/amazoncache.php");
		include("src/iamazonparser.php");
		include("src/amazonparser.php");
		include("src/iamazondb.php");
		include("src/amazondb.php");
		include("src/itransient.php");
		include("src/transient.php");
		include("src/iamazonproductsearch.php");
		include("src/amazonproductsearch.php");

		include("src/amazonmws.php");
		include("src/iamazonmwsreportrequest.php");
		include("src/amazonmwsreportrequest.php");
		include("src/iamazonmwsreportrequestlist.php");
		include("src/amazonmwsreportrequestlist.php");
		include("src/iamazonmwsreport.php");
		include("src/amazonmwsreport.php");



		global $conn;
		global $options;
		include("src/config.php");

		$options["MSWTestMode"] = true;


		$MWS = new AmazonMWS();
		$startDate = new \DateTime();
		$endDate = new \DateTime();


		$MWS->updatesSalesData();



	}

}
