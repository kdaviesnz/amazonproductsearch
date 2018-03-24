<?php
declare( strict_types=1 ); // must be first line


namespace kdaviesnz\amazon;



class AmazonMWSReportRequestList extends \AmazonReportRequestList implements IAmazonMWSReportRequestList {

	/*
	protected $tokenFlag = false;
    protected $tokenUseFlag = false;
    protected $index = 0;
    protected $i = 0;
    protected $reportList;
    protected $count;
	 */

	/**
	 * AmazonReportRequestList fetches a list of report requests from Amazon.
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
	public function __construct($s = null, $mock = false, $m = null, $config = null) {
		parent::__construct($s, $mock, $m, $config);
	}

	/**
	 * Fetches a list of Report Requests from Amazon.
	 *
	 * Submits a <i>GetReportRequestList</i> request to Amazon. Amazon will send
	 * the list back as a response, which can be retrieved using <i>getList</i>.
	 * Other methods are available for fetching specific values from the list.
	 * This operation can potentially involve tokens.
	 * @param boolean $r <p>When set to <b>FALSE</b>, the function will not recurse, defaults to <b>TRUE</b></p>
	 * @return boolean <b>FALSE</b> if something goes wrong
	 * @see https://docs.developer.amazonservices.com/en_DE/reports/Reports_GetReportRequestList.html
	 */
	public function fetchRequestList($r = true){
		try {
			$ok = parent::fetchRequestList( $r );
		} catch(\Exception $e) {
			$ok = false;
		}

		global $options;

		if (!$ok && $options["MSWTestMode"]) {
			// Load some default values
			$i = 0;
			$this->reportList[$i] = array();
			$this->reportList[$i]['ReportRequestId'] = "2291326454";
			$this->reportList[$i]['ReportType'] = "_GET_CONVERGED_FLAT_FILE_SOLD_LISTINGS_DATA_";
			$this->reportList[$i]['StartDate'] = (string)date("c");;
			$this->reportList[$i]['EndDate'] = (string)date("c");;
			$this->reportList[$i]['Scheduled'] = "false";
			$this->reportList[$i]['SubmittedDate'] = (string)date("c");
			$this->reportList[$i]['ReportProcessingStatus'] = "_DONE_";
			$this->reportList[$i]['GeneratedReportId'] = "3538561173";
			$this->reportList[$i]['StartedProcessingDate'] = (string)date("c");
			$this->reportList[$i]['CompletedDate'] = (string)date("c");
		}
	}

}