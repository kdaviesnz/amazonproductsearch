<?php
declare( strict_types = 1 );

namespace kdaviesnz\amazon;


class Transient implements ITransient {


// wp_cache_set()
// add_option( $transient_timeout, time() + $expiration, '', 'no' );
// update_option( $option, $new_value, $autoload );

	private $amazonDB;

	/**
	 * Transient constructor.
	 */
	public function __construct($conn) {
		$this->amazonDB = new AmazonDB($conn);
	}

	/**
		 * Set/update the value of a transient.
		 *
		 * You do not need to serialize values. If the value needs to be serialized, then
		 * it will be serialized before it is set.
		 *
		 * @since 2.8.0
		 *
		 * @param string $transient  Transient name. Expected to not be SQL-escaped. Must be
		 *                           172 characters or fewer in length.
		 * @param mixed  $value      Transient value. Must be serializable if non-scalar.
		 *                           Expected to not be SQL-escaped.
		 * @param int    $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
		 * @return bool False if value was not set and true if value was set.
	     * @see  https://core.trac.wordpress.org/browser/tags/4.9.4/src/wp-includes/option.php#L0 set_transient()
	 */
	public function save( String $transient, $value, int $expiration ):bool {

		$transient_timeout = '_transient_timeout_' . $transient;
		$transient_option = '_transient_' . $transient;

		if ( false === $this->get_option( $transient_option ) ) {

			$autoload = true;

			if ( $expiration ) {
				$autoload = 'no';
				$this->add_option( $transient_timeout, time() + $expiration, '', false );
			}
			$result = $this->add_option( $transient_option, $value, '', $autoload );

		} else {

			// If expiration is requested, but the transient has no timeout option,
			// delete, then re-create transient rather than update.
			$update = true;
			if ( $expiration ) {
				if ( false === $this->get_option( $transient_timeout ) ) {
					$this->delete_option( $transient_option );
					$this->add_option( $transient_timeout, time() + $expiration, '', false );
					$result = $this->add_option( $transient_option, $value, '', false );
					$update = false;
				} else {
					$this->update_option( $transient_timeout, time() + $expiration );
				}
			}
			if ( $update ) {
				$result = $this->update_option( $transient_option, $value );
			}


		}

		return $result;

	}

	public function fetch(String $transient) {

		//   $related_products = get_transient('related' . $product->getAsin() );
		$transient_option = '_transient_' . $transient;
		$transient_timeout = '_transient_timeout_' . $transient;
		$timeout = $this->get_option( $transient_timeout );
		if ( false !== $timeout && $timeout < time() ) {
			$this->delete_option( $transient_option  );
			$this->delete_option( $transient_timeout );
			$value = false;
		}

		if ( ! isset( $value ) )
			$value = $this->get_option( $transient_option );

		return $value;

	}

	private function add_option(String $option, $value, String $deprecated, bool $autoload) : bool {

		$option = trim($option);
		if ( empty($option) )
			return false;

		$serialized_value = $this->maybe_serialize( $value );
		$autoload = ( 'no' === $autoload || false === $autoload ) ? 'no' : 'yes';

		$result = $this->amazonDB->query( $this->amazonDB->prepare( "INSERT INTO `wp_amazon_options` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s) ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)", $option, $serialized_value, $autoload ) );

		return $result;

	}

	private function update_option( String $option, $value, bool $autoload = false ):bool {

        $option = trim($option);
	        if ( empty($option) )
			           return false;

		if ( is_object( $value ) )
			$value = clone $value;

		$old_value = $this->get_option( $option );

		if ( $value === $old_value || $this->maybe_serialize( $value ) === $this->maybe_serialize( $old_value ) ) {
			return false;
        }

		$serialized_value = $this->maybe_serialize( $value );

		$autoload = ( 'no' === $autoload || false === $autoload ) ? 'no' : 'yes';

		$result = $this->amazonDB->query("UPDATE `wp_amazon_options` SET `option_value` = '$serialized_value', `autoload` = '$autoload' WHERE `option_name` = '$option'");

		return $result;

	}

	private function get_option(String $option, $default = false) {

		$option = trim( $option );
		if ( empty( $option ) )
			return false;

		$row = $this->amazonDB->get_row( $this->amazonDB->prepare( "SELECT `option_value` FROM `wp_amazon_options` WHERE `option_name` = '$option'", ""));

		return empty($row) ? null : $this->maybe_unserialize($row['option_value']);

	}

	private function delete_option(String $option):bool {

		$option = trim( $option );
		if ( empty( $option ) )
			return false;

		$result = $this->amazonDB->query( $this->amazonDB->prepare( "DELETE FROM `wp_amazon_options` WHERE `option_name` = '$option'", ""));

		return $result;

	}

	private function maybe_serialize( $data ) {

	        if ( is_array( $data ) || is_object( $data ) )
	                return serialize( $data );

	        if ( $this->is_serialized( $data, false ) )
	                return serialize( $data );

	        return $data;
	}

	private function maybe_unserialize( $original ) {
		if ( $this->is_serialized( $original ) ) // don't attempt to unserialize data that wasn't serialized going in
			return @unserialize( $original );
		return $original;
	}

	private function is_serialized( $data, $strict = true ) {
		// if it isn't a string, it isn't serialized.
		if ( ! is_string( $data ) ) {
			return false;
		}
		$data = trim( $data );
		if ( 'N;' == $data ) {
			return true;
		}
		if ( strlen( $data ) < 4 ) {
			return false;
		}
		if ( ':' !== $data[1] ) {
			return false;
		}
		if ( $strict ) {
			$lastc = substr( $data, -1 );
			if ( ';' !== $lastc && '}' !== $lastc ) {
				return false;
			}
		} else {
			$semicolon = strpos( $data, ';' );
			$brace     = strpos( $data, '}' );
			// Either ; or } must exist.
			if ( false === $semicolon && false === $brace )
				return false;
			// But neither must be in the first X characters.
			if ( false !== $semicolon && $semicolon < 3 )
				return false;
			if ( false !== $brace && $brace < 4 )
				return false;
		}
		$token = $data[0];
		switch ( $token ) {
			case 's' :
				if ( $strict ) {
					if ( '"' !== substr( $data, -2, 1 ) ) {
						return false;
					}
				} elseif ( false === strpos( $data, '"' ) ) {
					return false;
				}
			// or else fall through
			case 'a' :
			case 'O' :
				return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
			case 'b' :
			case 'i' :
			case 'd' :
				$end = $strict ? '$' : '';
				return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
		}
		return false;
	}
}