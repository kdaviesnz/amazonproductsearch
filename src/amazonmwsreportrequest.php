<?php
declare( strict_types=1 );


namespace kdaviesnz\amazon;


class AmazonMWSReportRequest extends \AmazonReportRequest implements IAmazonMWSReportRequest {

	// array
	// protected $response

	/**
	 * AmazonReportRequest sends a report request to Amazon.
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
	 * Sends a report request to Amazon.
	 *
	 * Submits a <i>RequestReport</i> request to Amazon. In order to do this,
	 * a Report Type is required. Amazon will send info back as a response,
	 * which can be retrieved using <i>getResponse</i>.
	 * Other methods are available for fetching specific values from the list.
	 * @return boolean <b>FALSE</b> if something goes wrong
	 * @see http://docs.developer.amazonservices.com/en_US/reports/Reports_RequestReport.html
	 * Example raw response xml:
	 <?xml version="1.0"?>
	<RequestReportResponse
	xmlns="http://mws.amazonaws.com/doc/2009-01-01/">
	<RequestReportResult>
	<ReportRequestInfo>
	<ReportRequestId>2291326454</ReportRequestId>
	<ReportType>_GET_MERCHANT_LISTINGS_DATA_</ReportType>
	<StartDate>2009-01-21T02:10:39+00:00</StartDate>
	<EndDate>2009-02-13T02:10:39+00:00</EndDate>
	<Scheduled>false</Scheduled>
	<SubmittedDate>2009-02-20T02:10:39+00:00</SubmittedDate>
	<ReportProcessingStatus>_SUBMITTED_</ReportProcessingStatus>
	</ReportRequestInfo>
	</RequestReportResult>
	<ResponseMetadata>
	<RequestId>88faca76-b600-46d2-b53c-0c8c4533e43a</RequestId>
	</ResponseMetadata>
	</RequestReportResponse>
	 */
	public function requestReport() {


		// @see http://docs.developer.amazonservices.com/en_ES/reports/Reports_RequestReport.html
		try {
			// Returns false if there is an error
			$ok = parent::requestReport();
			//var_dump($this->getLastErrorMessage()); // Parameter AWSAccessKeyId cannot be empty
		} catch(\Exception $e) {
			var_dump($e->getMessage());
			$ok = false;
		}

		global $options;

		if (!$ok && $options["MSWTestMode"]) {
			// Load some default values
			$this->response = array();
			$this->response['ReportRequestId'] = "2291326454";
			$this->response['ReportType'] = (string)$this->options['ReportType'];
			$this->response['StartDate'] = (string)$this->options['StartDate'] ;
			$this->response['EndDate'] = (string)$this->options['EndDate'];
			$this->response['Scheduled'] = "false";
			$this->response['SubmittedDate'] = date('c');
			$this->response['ReportProcessingStatus'] = "SUBMITTED_";
		}

	}

}