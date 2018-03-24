<?php


if (isset($_SERVER["HTTP_HOST"]) && $_SERVER["HTTP_HOST"]=="premiumwebtechnologies.com") {
	$conn = mysqli_connect("localhost", "kdavies_jeffw", "7Hpd9ub5", "kdavies_jeffw");

} else {
	$conn = mysqli_connect("localhost", "root", "7Hpd9ub5", "kdavies_jeffw");
}

if (!$conn) {
	throw new Exception(("Could not connect to database"));
}

$options = array(
	'amazon_accounts' => array(
		array(
			'amazon_unique_name' => 'Bob',
			'amazon_secret_access_key' => 'Z0IMBN8mDpijLRe1sRnu/PUonU9V4RTEaFI6XyES',
			'amazon_access_key_id' => 'AKIAIUKL35B3SHB6WUOQ',
			'amazon_affiliate_link' => 'crosswordheav-20'
		)
	),
	'cache' => true
);

/*
$conn = mysqli_connect("localhost", "dbuser", "dbpassword", "dbname");

if (!$conn) {
	throw new Exception(("could not connect to database"));
}

$options = array(
	'amazon_accounts' => array(
		array(
			'amazon_unique_name' => '',
			'amazon_secret_access_key' => '',
			'amazon_access_key_id' => '',
			'amazon_affiliate_link' => ''
		)
	)
);
*/