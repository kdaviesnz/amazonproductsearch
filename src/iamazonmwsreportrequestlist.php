<?php


namespace kdaviesnz\amazon;


interface IAmazonMWSReportRequestList {

	// Methods are set by parent class

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
	 * Sets the report request ID(s). (Optional)
	 *
	 * This method sets the list of report request IDs to be sent in the next request.
	 * @param array|string $s <p>A list of report request IDs, or a single type string.</p>
	 * @return boolean <b>FALSE</b> if improper input
	 */
	public function setRequestIds($s);

	/**
	 * Removes report request ID options.
	 *
	 * Use this in case you change your mind and want to remove the Report Request ID
	 * parameters you previously set.
	 */
	public function resetRequestIds();

	/**
	 * Sets the report type(s). (Optional)
	 *
	 * This method sets the list of report types to be sent in the next request.
	 * @param array|string $s <p>A list of report types, or a single type string.</p>
	 * @return boolean <b>FALSE</b> if improper input
	 */
	public function setReportTypes($s);

	/**
	 * Removes report type options.
	 *
	 * Use this in case you change your mind and want to remove the Report Type
	 * parameters you previously set.
	 */
	public function resetReportTypes();

	/**
	 * Sets the report status(es). (Optional)
	 *
	 * This method sets the list of report types to be sent in the next request.
	 * @param array|string $s <p>A list of report types, or a single type string.</p>
	 * @return boolean <b>FALSE</b> if improper input
	 */
	public function setReportStatuses($s);

	/**
	 * Removes report status options.
	 *
	 * Use this in case you change your mind and want to remove the Report Status
	 * parameters you previously set.
	 */
	public function resetReportStatuses();

	/**
	 * Sets the maximum response count. (Optional)
	 *
	 * This method sets the maximum number of Report Requests for Amazon to return.
	 * If this parameter is not set, Amazon will only send 10 at a time.
	 * @param array|string $s <p>Positive integer from 1 to 100.</p>
	 * @return boolean <b>FALSE</b> if improper input
	 */
	public function setMaxCount($s);

	/**
	 * Sets the time frame options. (Optional)
	 *
	 * This method sets the start and end times for the next request. If this
	 * parameter is set, Amazon will only return Report Requests that were submitted
	 * between the two times given. If these parameters are not set, Amazon will
	 * only return Report Requests that were submitted within the past 90 days.
	 * The parameters are passed through <i>strtotime</i>, so values such as "-1 hour" are fine.
	 * @param string $s [optional] <p>A time string for the earliest time.</p>
	 * @param string $e [optional] <p>A time string for the latest time.</p>
	 */
	public function setTimeLimits($s = null,$e = null);

	/**
	 * Removes time limit options.
	 *
	 * Use this in case you change your mind and want to remove the time limit
	 * parameters you previously set.
	 */
	public function resetTimeLimits();

	/**
	 * Fetches a list of Report Requests from Amazon.
	 *
	 * Submits a <i>GetReportRequestList</i> request to Amazon. Amazon will send
	 * the list back as a response, which can be retrieved using <i>getList</i>.
	 * Other methods are available for fetching specific values from the list.
	 * This operation can potentially involve tokens.
	 * @param boolean $r <p>When set to <b>FALSE</b>, the function will not recurse, defaults to <b>TRUE</b></p>
	 * @return boolean <b>FALSE</b> if something goes wrong
	 */
	public function fetchRequestList($r = true);


	/**
	 * Cancels the report requests that match the given parameters. Careful!
	 *
	 * Submits a <i>CancelReportRequests</i> request to Amazon. Amazon will send
	 * as a response the list of feeds that were cancelled, along with the count
	 * of the number of affected feeds. This data can be retrieved using the same
	 * methods as with <i>fetchRequestList</i> and <i>fetchCount</i>.
	 * @return boolean <b>FALSE</b> if something goes wrong
	 */
	public function cancelRequests();

	/**
	 * Fetches a count of Report Requests from Amazon.
	 *
	 * Submits a <i>GetReportRequestCount</i> request to Amazon. Amazon will send
	 * the number back as a response, which can be retrieved using <i>getCount</i>.
	 * @return boolean <b>FALSE</b> if something goes wrong
	 */
	public function fetchCount();

	/**
	 * Returns the report request ID for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getRequestId($i = 0);

	/**
	 * Returns the report type for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getReportType($i = 0);


	/**
	 * Returns the start date for the specified report request.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getStartDate($i = 0);


	/**
	 * Returns the end date for the specified report request.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getEndDate($i = 0);


	/**
	 * Returns whether or not the specified report request is scheduled.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getIsScheduled($i = 0);


	/**
	 * Returns the date the specified report request was submitted.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getSubmittedDate($i = 0);


	/**
	 * Returns the processing status for the specified report request.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getStatus($i = 0);

	/**
	 * Returns the report ID for the specified entry.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getReportId($i = 0);


	/**
	 * Returns the date processing for the specified report request started.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getDateProcessingStarted($i = 0);


	/**
	 * Returns the date processing for the specified report request was finished.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 */
	public function getDateCompleted($i = 0);


	/**
	 * Alias of getDateCompleted.
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to 0.</p>
	 * @return string|boolean single value, or <b>FALSE</b> if Non-numeric index
	 * @see getDateCompleted
	 * @deprecated since 1.3.0
	 */
	public function getDateProcessingCompleted($i = 0);


	/**
	 * Returns the full list.
	 *
	 * This method will return <b>FALSE</b> if the list has not yet been filled.
	 * The array for a single report will have the following fields:
	 * <ul>
	 * <li><b>ReportRequestId</b></li>
	 * <li><b>ReportType</b></li>
	 * <li><b>StartDate</b></li>
	 * <li><b>EndDate</b></li>
	 * <li><b>Scheduled</b></li>
	 * <li><b>ReportProcessingStatus</b></li>
	 * <li><b>GeneratedReportId</b></li>
	 * <li><b>StartedProcessingDate</b></li>
	 * <li><b>CompletedDate</b></li>
	 * </ul>
	 * @param int $i [optional] <p>List index to retrieve the value from. Defaults to NULL.</p>
	 * @return array|boolean multi-dimensional array, or <b>FALSE</b> if list not filled yet
	 */
	public function getList($i = null);


	/**
	 * Returns the report request count.
	 *
	 * This method will return <b>FALSE</b> if the count has not been set yet.
	 * @return number|boolean number, or <b>FALSE</b> if count not set yet
	 */
	public function getCount();


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