<?php


namespace kdaviesnz\amazon;


interface IAmazonMWSReport {

	public function writeReport(String $generatedReportId);

	// These methods are set by the parent class

	/**
	 * Sets the report ID. (Required)
	 *
	 * This method sets the report ID to be sent in the next request.
	 * This parameter is required for fetching the report from Amazon.
	 * @param string|integer $n <p>Must be numeric</p>
	 * @return boolean <b>FALSE</b> if improper input
	 */
	public function setReportId($n);

	/**
	 * Sends a request to Amazon for a report.
	 *
	 * Submits a <i>GetReport</i> request to Amazon. In order to do this,
	 * a report ID is required. Amazon will send
	 * the data back as a response, which can be saved using <i>saveReport</i>.
	 * @return boolean <b>FALSE</b> if something goes wrong
	 */
	public function fetchReport();

	/**
	 * Gets the raw report data.
	 * This method will return <b>FALSE</b> if the data has not yet been retrieved.
	 * Please note that this data is often very large.
	 * @param string $path <p>filename to save the file in</p>
	 * @return string|boolean raw data string, or <b>FALSE</b> if data has not been retrieved yet
	 */
	public function getRawReport();

	/**
	 * Saves the raw report data to a path you specify
	 * @param string $path <p>filename to save the file in</p>
	 * @return boolean <b>FALSE</b> if something goes wrong
	 */
	public function saveReport($path);



}