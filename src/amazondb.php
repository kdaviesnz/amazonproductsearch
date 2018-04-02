<?php
declare( strict_types=1 ); // must be first line



namespace kdaviesnz\amazon;


class AmazonDB implements IAmazonDB {

	private $conn;
	public $last_error;

	/**
	 * AmazonDB constructor.
	 *
	 * @param $conn
	 */
	public function __construct($conn) {;
		$this->conn = $conn;
	}

	public function query( String $query ) {
		$ret = mysqli_query($this->conn, $query);
		$this->last_error = mysqli_error($this->conn);
		return $ret;
	}

	/**
	 * Prepares a SQL query for safe execution. Uses sprintf()-like syntax.
	 *
	 * The following directives can be used in the query format string:
	 *   %d (integer)
	 *   %f (float)
	 *   %s (string)
	 *   %% (literal percentage sign - no argument needed)
	 *
	 * All of %d, %f, and %s are to be left unquoted in the query string and they need an argument passed for them.
	 * Literals (%) as parts of the query must be properly written as %%.
	 *
	 * This function only supports a small subset of the sprintf syntax; it only supports %d (integer), %f (float), and %s (string).
	 * Does not support sign, padding, alignment, width or precision specifiers.
	 * Does not support argument numbering/swapping.
	 *
	 * May be called like {@link https://secure.php.net/sprintf sprintf()} or like {@link https://secure.php.net/vsprintf vsprintf()}.
	 *
	 * Both %d and %s should be left unquoted in the query string.
	 *
	 *     wpdb::prepare( "SELECT * FROM `table` WHERE `column` = %s AND `field` = %d", 'foo', 1337 )
	 *     wpdb::prepare( "SELECT DATE_FORMAT(`field`, '%%c') FROM `table` WHERE `column` = %s", 'foo' );
	 *
	 * @link https://secure.php.net/sprintf Description of syntax.
	 * @since 2.3.0
	 *
	 * @param string      $query    Query statement with sprintf()-like placeholders
	 * @param array|mixed $args     The array of variables to substitute into the query's placeholders if being called like
	 *                              {@link https://secure.php.net/vsprintf vsprintf()}, or the first variable to substitute into the query's placeholders if
	 *                              being called like {@link https://secure.php.net/sprintf sprintf()}.
	 * @param mixed       $args,... further variables to substitute into the query's placeholders if being called like
	 *                              {@link https://secure.php.net/sprintf sprintf()}.
	 * @return string|void Sanitized query string, if there is a query to prepare.
	 */
	public function prepare( String $query, String $args ) : String{

		if ( is_null( $query ) )
			return "";

		if (empty($args)) {
			return $query;
		}

		$args = func_get_args();
		array_shift( $args );
		// If args were passed as an array (as in vsprintf), move them up
		if ( isset( $args[0] ) && is_array($args[0]) )
			$args = $args[0];
		$query = str_replace( "'%s'", '%s', $query ); // in case someone mistakenly already singlequoted it
		$query = str_replace( '"%s"', '%s', $query ); // doublequote unquoting
		$query = preg_replace( '|(?<!%)%f|' , '%F', $query ); // Force floats to be locale unaware
		$query = preg_replace( '|(?<!%)%s|', "'%s'", $query ); // quote the strings, avoiding escaped strings like %%s
		array_walk( $args, array( $this, 'escape_by_ref' ) );
		return @vsprintf( $query, $args );
	}

	public function get_results(String $query, $output = "OBJECT") {

		$result = mysqli_query($this->conn, $query);

		if (is_bool($result)) {
			echo($query);
			throw new \Exception(("Database error:".mysqli_error($this->conn)));
		}

		$results = array();
		if ($output == "OBJECT") {
			while($row = mysqli_fetch_object($result)){
				$results[] = $row;
			}
		} else {
			$row = mysqli_fetch_assoc($result);
			while($row = mysqli_fetch_object($result)){
				$results[] = $row;
			}
		}

		return $results;

	}

	public function get_row(String $query = "", $output = "OBJECT") {
		$result = mysqli_query($this->conn, $query);
		$this->last_error = mysqli_error($this->conn);
		if (!empty($this->last_error)) {
			throw new Exception($this->last_error . ": " . $query);
		}
		if ($output == "OBJECT") {
			$row = mysqli_fetch_object($result);
		} else {
			$row = mysqli_fetch_assoc($result);
		}
		return $row;
	}

	public function escape_by_ref( &$string ) {
		if ( ! is_float( $string ) )
			$string = mysqli_real_escape_string($this->conn, (String)$string);
	}
}