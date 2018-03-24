<?php


namespace kdaviesnz\amazon;
require_once("src/iamazondb.php");
require_once("src/amazondb.php");
require_once("src/itransient.php");
require_once("src/transient.php");


class TransientTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
	}

	public function tearDown() {

	}

	public function testMinimumViableTest() {
		/** @noinspection PhpUndefinedMethodInspection */
		$this->assertTrue( true, "true didn't end up being false!" );
	}


	public function testSave() {

		global $conn;
		global $options;
		include("src/config.php");
		$t = new Transient($conn);

		$t->save("greeting", "hello there", 0);

	}

	public function testFetch() {

		global $conn;
		global $options;
		include("src/config.php");;
		$t = new Transient($conn);
		$v = $t->fetch("greeting");
		$this->assertTrue( $v!=false, "Fetch not working");

	}

}
