<?php


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
