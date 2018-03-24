<?php
declare( strict_types=1 );



namespace kdaviesnz\amazon;


class AmazonMWS {


	public function updatesSalesData() {

		//30 days, 6 months, 12 months

		// Get report data for the last 12 months
		$endDate = new \DateTime();
		$interval = new \DateInterval("P1Y");
		$startDate = $endDate->add($interval);

		$reportId = $this->generateEstimatedSalesReport($startDate, $endDate);

		// Loop over the CSV file and extract sales data for each product.
		$salesData = array();

		$headings = array();

		foreach ($this->getReportRows("src/reports/" . $reportId . ".csv") as $row) {
			$fieldValues = explode("\t", $row[0]);
			if (array_search("order-id", $fieldValues )!==false){
				$headings = $fieldValues;
			} else {
				if (count($headings) > count($fieldValues)) {
					// pad out $fields so it matches $headings
					$diff = count($headings) - count($fieldValues);
					for ($i=0;$i<$diff;$i++) {
						$fieldValues[] = "";
					}
				}
				$fields = array_combine($headings, $fieldValues);

				// Format the sales data
				// @see https://docs.aws.amazon.com/AWSECommerceService/latest/DG/OtherItemIdentifiers.html
				//var_dump($fields);
				if (!isset($salesData[$fields["sku"]])) {
					$salesData[$fields["sku"]] = array();
				}

				if (!isset($salesData[$fields["sku"]][$fields["currency"]])) {
					$salesData[$fields["sku"]][$fields["currency"]] = array();
				}

				$salesData[$fields["sku"]][$fields["currency"]][] = array(
					"quantity-purchased" => $fields["quantity-purchased"],
					"item-price" => $fields["item-price"],
					"item-tax" => $fields["item-tax"],
					"shipping-price" => $fields["shipping-price"],
					"shipping-tax" => $fields["shipping-tax"],
					"purchase-date" => $fields["purchase-date"]
				);

			}
		}

		// Load data
		foreach ($salesData as $sku=>$data) {

			$t30days = array_reduce(
				$data[$fields["currency"]],
				function($carry, $item) {
					$purchaseDate = new \DateTime($item["purchase-date"]);
					$timeNow = new \DateTime();
					$interval = $purchaseDate->diff($timeNow);
					return $interval->days > 30?$carry:$carry +  ($item["item-price"] * $item["quantity-purchased"]);
				}
			);

			$t6months = array_reduce(
				$data[$fields["currency"]],
				function($carry, $item) {
					$purchaseDate = new \DateTime($item["purchase-date"]);
					$timeNow = new \DateTime();
					$interval = $purchaseDate->diff($timeNow);
					return $interval->days > (30 * 6)?$carry:$carry +  ($item["item-price"] * $item["quantity-purchased"]);
				}
			);

			$t12months = array_reduce(
				$data[$fields["currency"]],
				function($carry, $item) {
					$purchaseDate = new \DateTime($item["purchase-date"]);
					$timeNow = new \DateTime();
					$interval = $purchaseDate->diff($timeNow);
					return $interval->days > (30 * 12)?$carry:$carry +  ($item["item-price"] * $item["quantity-purchased"]);
				}
			);

			$t30daysSalesCount = array_reduce(
				$data[$fields["currency"]],
				function($carry, $item) {
					if (empty($carry)) {
						return $item["item-price"] * $item["quantity-purchased"];
					}
					$purchaseDate = new \DateTime($item["purchase-date"]);
					$timeNow = new \DateTime();
					$interval = $purchaseDate->diff($timeNow);
					return $interval->days > 30?$carry:$carry + $item["quantity-purchased"];
				}
			);

			$t6monthsSalesCount = array_reduce(
				$data[$fields["currency"]],
				function($carry, $item) {
					$purchaseDate = new \DateTime($item["purchase-date"]);
					$timeNow = new \DateTime();
					$interval = $purchaseDate->diff($timeNow);
					return $interval->days > (30 * 6)?$carry:$carry + $item["quantity-purchased"];
				}
			);

			$t12monthsSalesCount = array_reduce(
				$data[$fields["currency"]],
				function($carry, $item) {
					$purchaseDate = new \DateTime($item["purchase-date"]);
					$timeNow = new \DateTime();
					$interval = $purchaseDate->diff($timeNow);
					return $interval->days > (30 * 12)?$carry:$carry + $item["quantity-purchased"];
				}
			);

			// Get item match sku
			// This also adds record to database if required
			$product = \kdaviesnz\amazon\AmazonProductSearch::itemSearch(
				$sku,
				'SKU'
			);


			if ($product) {

				$salesData = array(
					"30days" => $t30days,
					"6months" => $t6months,
					"12months" => $t12months,
					"30daysSalesCount" => $t30daysSalesCount,
					"6monthsSalesCount" => $t6monthsSalesCount,
					"12monthsSalesCount" => $t12monthsSalesCount,
				);
				$product->addSalesData($salesData);
			}

		}

	}

	/**
	 * Generate estimated sales report
	 *
	 * @return mixed
	 * @see vendor/cpigroup/php-amazon-mws/includes/AmazonReportRequest.php
	 * @see http://www.ifourtechnolab.com/blog/estimated-sales-report-generation-through-amazon-api-mws-marketplace-api
	 */
	public function generateEstimatedSalesReport(\DateTime $startDate, \DateTime $endDate): String {

		/*
		 First step is to call ReportRequest method from MWS. Start_Date and End_Date are passed as parameters. Default value is current date and time. This returns the data from where the last settlement report was generated. This method will return the ReportRequestId List.
		 */
		$reportRequest = new AmazonMWSReportRequest();
		$reportRequest->setReportType('_GET_CONVERGED_FLAT_FILE_SOLD_LISTINGS_DATA_');
		$reportRequest->setTimeLimits($startDate->format('c'), $endDate->format('c'));
		$reportRequest->requestReport();
		$report = $reportRequest->getResponse(); // array

		/*
		 $report:
array(7) {
  ["ReportRequestId"]=>
  string(10) "2291326454"
  ["ReportType"]=>
  string(44) "_GET_CONVERGED_FLAT_FILE_SOLD_LISTINGS_DATA_"
  ["StartDate"]=>
  string(25) "2018-03-23T01:11:56+01:00"
  ["EndDate"]=>
  string(25) "2018-03-23T01:11:56+01:00"
  ["Scheduled"]=>
  string(5) "false"
  ["SubmittedDate"]=>
  string(25) "2018-03-23T01:13:57+01:00"
  ["ReportProcessingStatus"]=>
  string(10) "_SUBMITTED_"
}
		 */


		/*
		 Second step is to call the GetReportRequestList method recursively until the ReportRequestId field has status “_DONE_”. When we get the matched status for the ReportRequestId, it returns the List of GeneratedReportId.
		@see https://docs.developer.amazonservices.com/en_DE/reports/Reports_GetReportRequestList.html
		Example response (GetRequestsList)
		<?xml version="1.0"?>
<GetReportRequestListResponse
    xmlns="http://mws.amazonservices.com/doc/2009-01-01/">
    <GetReportRequestListResult>
        <NextToken>2YgYW55IPQhcm5hbCBwbGVhc3VyZS4=</NextToken>
        <HasNext>true</HasNext>
        <ReportRequestInfo>
            <ReportRequestId>2291326454</ReportRequestId>
            <ReportType>_GET_MERCHANT_LISTINGS_DATA_</ReportType>
            <StartDate>2011-01-21T02:10:39+00:00</StartDate>
            <EndDate>2011-02-13T02:10:39+00:00</EndDate>
            <Scheduled>false</Scheduled>
            <SubmittedDate>2011-02-17T23:44:09+00:00</SubmittedDate>
            <ReportProcessingStatus>_DONE_</ReportProcessingStatus>
            <GeneratedReportId>3538561173</GeneratedReportId>
            <StartedProcessingDate>
                2011-02-17T23:44:43+00:00
            </StartedProcessingDate>
            <CompletedDate>2011-02-17T23:44:48+00:00</CompletedDate>
        </ReportRequestInfo>
    </GetReportRequestListResult>
    <ResponseMetadata>
        <RequestId>732480cb-84a8-4c15-9084-a46bd9a0889b</RequestId>
    </ResponseMetadata>
</GetReportRequestListResponse>
		 */

		$reportRequestList = new AmazonMWSReportRequestList();
		$reportRequestList->setUseToken(true);
		$reportRequestList->fetchRequestList();
		$requestList = $reportRequestList->getList();

//		var_dump($requestList);
		/*
		 array(1) {
		  [0]=>
		  array(10) {
			["ReportRequestId"]=>
			string(10) "2291326454"
			["ReportType"]=>
			string(44) "_GET_CONVERGED_FLAT_FILE_SOLD_LISTINGS_DATA_"
			["StartDate"]=>
			string(25) "2018-03-23T01:49:40+01:00"
			["EndDate"]=>
			string(25) "2018-03-23T01:49:40+01:00"
			["Scheduled"]=>
			string(5) "false"
			["SubmittedDate"]=>
			string(25) "2018-03-23T01:49:40+01:00"
			["ReportProcessingStatus"]=>
			string(6) "_DONE_"
			["GeneratedReportId"]=>
			string(10) "3538561173"
			["StartedProcessingDate"]=>
			string(25) "2018-03-23T01:49:40+01:00"
			["CompletedDate"]=>
			string(25) "2018-03-23T01:49:40+01:00"
		  }
		}
		 */
		// Get the row from $requestList which has the ReportProcessingStatus set to "_DONE_"
		$rows = array_filter($requestList, function($row) {
			return $row["ReportProcessingStatus"] == "_DONE_";
		});
		$generatedReportId = $rows[0]["GeneratedReportId"];

		/*
		 Check that the GeneratedReportId is present in our system to avoid duplicate entry and use GetReportRequest method to generate the report. If the GeneratedReportId is not present in the system we use the GetReportRequest and we  pass GeneratedReportId as the parameter. It returns data in XML format.
		@see http://docs.developer.amazonservices.com/en_AU/reports/Reports_GetReport.html ?
		 */
		$report = new AmazonMWSReport();
		$report->setReportId($generatedReportId);
		$report->fetchReport();

		// Save report
		$report->writeReport();

		return $generatedReportId;

	}

	private function getReportRows($file) {
		$handle = fopen($file, 'rb'); if ($handle === false) {
			throw new Exception();
		}

		while (feof($handle) === false) {
			yield fgetcsv($handle);
		}
		fclose($handle);
	}

	private function genSalesData(array $salesData) {
		foreach ($salesData as $data) {
			yield $data;
		}
	}


}