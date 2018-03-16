<?php



namespace kdaviesnz\amazon;


/**
 * Class AmazonGeo
 *
 * @package kdaviesnz\amazon
 *
 */
class AmazonGeo implements IAmazonGeo
{

	/**
	 * Country
	 *
	 * @var string
	 */
	private $country = '';

	/**
	 * City
	 *
	 * @var string
	 */
	private $city = '';

	/**
	 * Region
	 *
	 * @var string
	 */
	private $region = '';

	/**
	 * Region code
	 *
	 * @var string
	 */
	private $region_code = '';

	/**
	 * Area code
	 *
	 * @var string
	 */
	private $area_code = '';

	/**
	 * Country code
	 *
	 * @var string
	 */
	private $country_code = '';

	/**
	 * Continent code
	 *
	 * @var string
	 */
	private $continent_code = '';

	/**
	 * Latitude
	 *
	 * @var float
	 */
	private $latitude = 0.00;

	/**
	 * Longitude
	 *
	 * @var float
	 */
	private $longitude = 0.00;

	/**
	 * Currency code
	 *
	 * @var string
	 */
	private $currency_code = '';

	/**
	 * Currency symbol
	 *
	 * @var string
	 */
	private $currency_symbol = '';

	/**
	 * Utf8 currency symbol
	 *
	 * @var string
	 */
	private $currency_symbol_utf8 = '';

	/**
	 * Currency converter
	 *
	 * @var float
	 */
	private $currrency_converter = 0.00;

	/**
	 * Ip
	 *
	 * @var string
	 */
	private $ip = '';

	/**
	 * AmazonGeo constructor.
	 *
	 * @param string $ip ip address.
	 */
	public function __construct( $ip = '' ) {

		if ( empty ( $ip ) && isset( $_SERVER ) ) {

			$client = stripslashes( isset( $_SERVER['HTTP_CLIENT_IP'] ) ? sanitize_key( $_SERVER['HTTP_CLIENT_IP'] ) : '' );
			$forward = stripslashes( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? sanitize_key( $_SERVER['HTTP_X_FORWARDED_FOR'] ) : '' );
			$remote = stripslashes( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_key( $_SERVER['REMOTE_ADDR'] ) : '' );

			if ( filter_var( $client, FILTER_VALIDATE_IP ) ) {
				$ip = $client;
			} elseif ( filter_var( $forward, FILTER_VALIDATE_IP ) ) {
				$ip = $forward;
			} else {
				$ip = $remote;
			}
		}

		if ( empty( $ip ) ) {
			$ip = '27.252.176.101';
		}

		// First, let's check the database.
		global $conn;
		$amazondb = new AmazonDB($conn);
		$sql = $amazondb->prepare(
			"SELECT `ip`, 
				`country` as `geoplugin_countryName`,	
				`countryCode` as `geoplugin_countryCode`,
				`region` as `geoplugin_region`,
				`regionCode` as `geoplugin_regionCode`,
				`city` as `geoplugin_city`,
				`areaCode` as `geoplugin_areaCode`,
				`continentCode` as `geoplugin_continentCode`,
				`latitude` as `geoplugin_latitude`,
				`longitude` as `geoplugin_longitude`,
				`currencyCode` as `geoplugin_currencyCode`,
				`currencySymbol` as `geoplugin_currencySymbol`,
				`currencySymbolUtf8` as `geoplugin_currencySymbol_UTF8`,
				`currencyConverter` as `geoplugin_curencyConverter`,
				`amazonCountryExt`
				FROM `wp_genesis_geo`
				WHERE `ip` = '%s'",
			$ip
		);
		$ip_data = $amazondb->get_row( $sql );
		$error = $amazondb->last_error;
		if ( ! empty( $error ) ) {
			echo $sql;
			throw new \Exception( $error );
		}

		if ( empty( $ip_data ) ) {
			$ip_data = json_decode(file_get_contents('http://www.geoplugin.net/json.gp?ip=' . $ip));
			// IP we haven't seen before, so let's save it to the database.
			$sql = $amazondb->prepare( "INSERT IGNORE INTO `wp_genesis_geo` (
						  `ip`, 
						  `country`, 
						  `countryCode`, 
						  `region`, 
						  `regionCode`,
						  `city`, 
						  `areaCode`, 
						  `continentCode`, 
						  `latitude`, 
						  `longitude`, 
						  `currencyCode`, 
						  `currencySymbol`, 
						  `currencySymbolUtf8`, 
						  `currencyConverter`,
						  `amazonCountryExt`
						  ) 
						  VALUES (
						  	'%s', 
						  	'%s', 
						  	'%s', 
						  	'%s', 
						  	'%s',
						  	'%s',
						  	'%s', 
						  	'%s', 
						  	'%s', 
						  	'%s', 
						  	'%s', 
						  	'%s', 
						  	'%s', 
						  	'%s', 
						  	'%s'
						  	);",
					$ip,
					$ip_data->geoplugin_countryName,
					$ip_data->geoplugin_countryCode,
				    $ip_data->geoplugin_region,
					$ip_data->geoplugin_regionCode,
					$ip_data->geoplugin_city,
					$ip_data->geoplugin_areaCode,
					$ip_data->geoplugin_continentCode,
					$ip_data->geoplugin_latitude,
					$ip_data->geoplugin_longitude,
					$ip_data->geoplugin_currencyCode,
					$ip_data->geoplugin_currencySymbol,
					$ip_data->geoplugin_currencySymbol_UTF8,
					property_exists($ip_data, '')?$ip_data->geoplugin_curencyConverter:'',
					property_exists($ip_data, '')?$ip_data->amazonCountryExt:''
			);

			$amazondb->query( $sql );
			$error = $amazondb->last_error;
			if ( ! empty( $error ) ) {
				echo $sql;
				throw new \Exception( $error );
			}
		}


		if ( $ip_data  ) {

			$this->country = ! empty( $ip_data->geoplugin_countryName ) ? $ip_data->geoplugin_countryName : '';
			$this->city = ! empty( $ip_data->geoplugin_city ) ? $ip_data->geoplugin_city: '';
			$this->region = ! empty( $ip_data->geoplugin_region) ? $ip_data->geoplugin_region : '';
			$this->region_code = ! empty( $ip_data->geoplugin_regionCode) ? $ip_data->geoplugin_regionCode : '';
			$this->area_code = ! empty( $ip_data->geoplugin_areaCode) ? $ip_data->geoplugin_areaCode : '';
			$this->country_code = ! empty( $ip_data->geoplugin_countryCode) ? $ip_data->geoplugin_countryCode : '';
			$this->continent_code = ! empty( $ip_data->geoplugin_continentCode) ? $ip_data->geoplugin_continentCode : '';
			$this->latitude = ! empty( $ip_data->geoplugin_latitude ) ?  $ip_data->geoplugin_latitude * 1.00 : 0.00;
			$this->longitude = ! empty( $ip_data->geoplugin_longitude ) ? $ip_data->geoplugin_longitude * 1.00 : 0.00;
			$this->currency_code = ! empty( $ip_data->geoplugin_currencyCode) ? $ip_data->geoplugin_currencyCode : '';
			$this->currency_symbol = ! empty( $ip_data->geoplugin_currencySymbol ) ? $ip_data->geoplugin_currencySymbol : '';
			$this->currency_symbol_utf8 = ! empty( $ip_data->geoplugin_currencySymbol_UTF8 ) ? $ip_data->geoplugin_currencySymbol_UTF8 : '';
			$this->currrency_converter = ! empty( $ip_data->geoplugin_curencyConverter ) ? $ip_data->geoplugin_curencyConverter : '';
			$this->ip = $ip;

		}

	}

	/**
	 * Get country.
	 *
	 * @return string
	 */
	public function getCountry() {
		return $this->country;
	}

	/**
	 * Get city.
	 *
	 * @return string
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * Get region.
	 *
	 * @return string
	 */
	public function getRegion() {
		return $this->region;
	}

	/**
	 * Get area code.
	 *
	 * @return string
	 */
	public function getAreaCode() {
		return $this->area_code;
	}

	/**
	 * Get country code.
	 *
	 * @return string
	 */
	public function getCountryCode() {
		return $this->country_code;
	}

	/**
	 * Get continent code.
	 *
	 * @return string
	 */
	public function getContinentCode() {
		return $this->continent_code;
	}

	/**
	 * Get latitude.
	 *
	 * @return float
	 */
	public function getLatitude() {
		return $this->latitude;
	}

	/**
	 * Get longitude.
	 *
	 * @return float
	 */
	public function getLongitude() {
		return $this->longitude;
	}

	/**
	 * Get currency code.
	 *
	 * @return string
	 */
	public function getCurrencyCode() {
		return $this->currency_code;
	}

	/**
	 * Get currency symbol.
	 *
	 * @return string
	 */
	public function getCurrencySymbol() {
		return $this->currency_symbol;
	}

	/**
	 * Get currency symbol in UTF8 format.
	 *
	 * @return string
	 */
	public function getCurrencySymbolUtf8() {
		return $this->currency_symbol_utf8;
	}

	/**
	 * Get currency converter.
	 *
	 * @return float
	 */
	public function getCurrrencyConverter() {
		return $this->currrency_converter;
	}

	/**
	 * Get IP.
	 *
	 * @return string
	 */
	public function getIp() {
		return $this->ip;
	}

	/**
	 * Get region code.
	 *
	 * @return int
	 */
	public function getRegionCode() {
		return $this->region_code;
	}


	/**
	 * Get Amazon country extension.
	 *
	 * @return string
	 */
	public function getAmazonCountryExt() {

		$country_code = $this->getCountryCode();

		switch ( $country_code ) {

			case 'IN':
				$amazon_country_ext = 'in';
				break;
			case 'US':
				$amazon_country_ext = 'com';
				break;
			case 'CA':
				$amazon_country_ext = 'ca';
				break;
			case 'GB':
				$amazon_country_ext = 'co.uk';
				break;
			case 'CN':
				$amazon_country_ext = 'joyo.com';
				break;
			case 'JP':
				$amazon_country_ext = 'jp';
				break;
			case 'FR':
				$amazon_country_ext = 'fr';
				break;
			case 'DE':
				$amazon_country_ext = 'de';
				break;
			case 'IT':
				$amazon_country_ext = 'it';
				break;
			case 'ES':
				$amazon_country_ext = 'es';
				break;
			case 'AT':
				$amazon_country_ext = 'at';
				break;
			case 'AU':
				$amazon_country_ext = 'com.au';
				break;
			case 'BR':
				$amazon_country_ext = 'com.br';
				break;
			default:
				$amazon_country_ext = 'com';
		}

		return $amazon_country_ext;

	}

	public function getAmazonLang() {

		$amazon_ext = $this->getAmazonCountryExt();
		$am_lang = 'en_US';

		// http://www.roseindia.net/tutorials/I18N/locales-list.shtml
		switch ( $amazon_ext ) {

			case 'in':
				$am_lang = 'en_In';
				break;
			case 'com':
				$am_lang = 'en_US';
				break;
			case 'ca':
				$am_lang = 'en_CA';
				break;
			case 'co.uk':
				$am_lang = 'en_GB';
				break;
			case 'joyo.com': // china
				$am_lang = 'zh_CN';
				break;
			case 'jp':
				$am_lang = 'ja_JP';
				break;
			case 'fr':
				$am_lang = 'fr';
				break;
			case 'de':
				$am_lang = 'de';
				break;
			case 'it':
				$am_lang = 'it';
				break;
			case 'es':
				$am_lang = 'es';
				break;
			case 'at':
				$am_lang = 'de';
				break;
			case 'au':
				$am_lang = 'en_AU';
				break;
			case 'br':
				$am_lang = 'en_UK';
				break;
			default:
				$am_lang = 'en_US';
		}

		return $am_lang;

	}


	/**
	 * @param string $address
	 * @return string
	 * @see https://colinyeoh.wordpress.com/2013/02/12/simple-php-function-to-get-coordinates-from-address-through-google-services/
     */
	public static function get_coordinates( $address ) {

		$address = str_replace(" ", "+", $address); // replace all the white space with "+" sign to match with google search pattern

		$url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=$address";

		$response = file_get_contents($url);

		$json = json_decode($response,TRUE); //generate array object from the response from the web

		return ($json['results'][0]['geometry']['location']['lat'].",".$json['results'][0]['geometry']['location']['lng']);

	}
}

