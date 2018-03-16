<?php


namespace kdaviesnz\amazon;


interface IAmazonDB {

	public function prepare(String $sql, String $args):String;
	public function get_results(String $query, $output = OBJECT);
	public function query(String $query);
	public function get_row(String $query = "", $output = OBJECT);
}