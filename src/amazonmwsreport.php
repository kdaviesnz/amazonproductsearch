<?php
declare( strict_types=1 ); // must be first line

namespace kdaviesnz\amazon;

use PHPUnit\Runner\Exception;

class AmazonMWSReport extends \AmazonReport implements IAmazonMWSReport{

	// protected $rawreport;

	/**
	 * AmazonReport fetches a report from Amazon.
	 *
	 * The parameters are passed to the parent constructor, which are
	 * in turn passed to the AmazonCore constructor. See it for more information
	 * on these parameters and common methods.
	 * Please note that an extra parameter comes before the usual Mock Mode parameters,
	 * so be careful when setting up the object.
	 * @param string $s [optional] <p>Name for the store you want to use.
	 * This parameter is optional if only one store is defined in the config file.</p>
	 * @param string $id [optional] <p>The report ID to set for the object.</p>
	 * @param boolean $mock [optional] <p>This is a flag for enabling Mock Mode.
	 * This defaults to <b>FALSE</b>.</p>
	 * @param array|string $m [optional] <p>The files (or file) to use in Mock Mode.</p>
	 * @param string $config [optional] <p>An alternate config file to set. Used for testing.</p>
	 */
	public function __construct($s = null, $id = null, $mock = false, $m = null, $config = null) {
		parent::__construct($s, $id, $mock, $m, $config);
	}

	/**
	 * Sends a request to Amazon for a report.
	 *
	 * Submits a <i>GetReport</i> request to Amazon. In order to do this,
	 * a report ID is required. Amazon will send
	 * the data back as a response, which can be saved using <i>saveReport</i>.
	 * @return boolean <b>FALSE</b> if something goes wrong
	 */
	public function fetchReport(){


		try {
			$ok = parent::fetchReport();
		} catch (\Exception $e) {
			$ok = false;
		}

		global $options;


		if (!$ok && $options["MSWTestMode"]) {

			// Use defaults.
			$path = "src/mock/soldlistings.csv";
			if (!is_file($path)) {
				$path = "mock/soldlistings.csv";
			}

			if (!is_file($path)) {
				$path = "../" . $path;
			}

			$this->rawreport = file_get_contents($path);
		}
	}

	public function writeReport(String $generatedReportId) {


		$path = "src/reports/";
		if (!is_dir("src")) {
			$path = "../" . $path;
		}

		if (!is_dir($path)) {
			mkdir($path);
		}

		$path = $path . $generatedReportId . ".csv";
		
		parent::saveReport($path);

	}

}