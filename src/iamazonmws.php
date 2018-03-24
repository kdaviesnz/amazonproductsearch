<?php


namespace kdaviesnz\amazon;


interface IAmazonMWS {


	/**
	 * Generate estimated sales report
	 * @return mixed
	 * @see http://www.ifourtechnolab.com/blog/estimated-sales-report-generation-through-amazon-api-mws-marketplace-api
	 */
	public function generateEstimatedSalesReport(\DateTime $startDate, \DateTime $endDate);


}