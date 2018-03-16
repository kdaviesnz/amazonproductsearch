<?php


namespace kdaviesnz\amazon;

/**
 * Class AmazonSettings
 *
 * @package kdaviesnz\amazon
 */
/**
 * Class AmazonSettings
 *
 * @package kdaviesnz\amazon
 */
class AmazonSettings implements ISettings
{

    public static function verify() {
        return ! empty( AmazonSettings::amazon_accounts()->value() );
    }

	/**
	 * @param string $name
	 * @return ISetting
	 */
	public static function get( $name ) {
		$option = Option::getOption($name);
		return new Setting( 'amazon', $name, $option );
	}

	/**
	 * @param ISetting $setting
	 * @return bool
	 */
	public static function set( ISetting $setting ) {
		return $setting->save();
	}

	/**
	 * @return array
	 */
	public static function all() {
		return array(
			'amazon_unique_name' => AmazonSettings::amazon_unique_name()->value()?AmazonSettings::amazon_unique_name()->value():"",
			'amazon_secret_access_key' => AmazonSettings::amazon_secret_access_key()->value()?AmazonSettings::amazon_secret_access_key()->value():"",
			'amazon_access_key_id' => AmazonSettings::amazon_access_key_id()->value()?AmazonSettings::amazon_access_key_id()->value():"",
			'amazon_affiliate_link' => AmazonSettings::amazon_affiliate_link()->value()?AmazonSettings::amazon_affiliate_link()->value():"",
            'amazon_accounts' => AmazonSettings::amazon_accounts()->value()?AmazonSettings::amazon_accounts()->value():array(),
		);
	}

	public static function reset() {
	    // @todo
        return true;
    }

	/**
	 * @param array $values
	 * @return bool
	 */
	public static function save( $values )
    {

        if ( isset( $values['amazon_unique_name'] ) && $values['amazon_unique_name'] ) {

            AmazonSettings::set_amazon_unique_name( $values['amazon_unique_name'] );
            AmazonSettings::set_amazon_secret_access_key( $values['amazon_secret_access_key'] );
            AmazonSettings::set_amazon_access_key_id(  $values['amazon_access_key_id'] );
            AmazonSettings::set_amazon_affiliate_link( $values['amazon_affiliate_link'] );

            if ( !isset( $values['amazon_accounts'] ) ) {
                $values['amazon_accounts'] =   array(
                        'amazon_unique_name' => $values['amazon_unique_name'],
                        'amazon_secret_access_key' => $values['amazon_secret_access_key'],
                        'amazon_access_key_id' => $values['amazon_access_key_id'] ,
                        'amazon_affiliate_link' => $values['amazon_affiliate_link'],
                    );
            }

        }

        AmazonSettings::add_amazon_account(
            $values['amazon_accounts']
        );

		return true;
	}

    public static function add_amazon_account( $account ) {
        AmazonSettings::set( new Setting( 'amazon', 'amazon_accounts', $account ) );
    }

    public static function update_amazon_account( $account_id_to_match, $updated_account ) {
        $accounts = AmazonSettings::amazon_accounts()->value();
        foreach( $accounts as $key=>$account ) {
            if ( $account_id_to_match == $account[ 'amazon_unique_name'] ) {
                $accounts[$key] = $updated_account;
                update_option( 'amazon_accounts', $accounts );
                break;
            }
        }
    }

    public static function delete_amazon_account( $account_id ) {
        $accounts = AmazonSettings::amazon_accounts()->value();
        foreach( $accounts as $key=>$account ) {
            if ( $account_id == $account[ 'amazon_unique_name'] ) {
                unset( $accounts[$key]);
                $accounts = array_values( $accounts );
                delete_option( 'amazon_accounts' );
                if ( !empty( $accounts ) ) {
                    //   AmazonSettings::add_amazon_account($accounts);
                    update_option( 'amazon_accounts', $accounts );
                }
                break;
            }
        }
    }

	/**
	 * @param string $unique_name
	 */
	public static function set_amazon_unique_name($unique_name ) {
		AmazonSettings::set( new Setting( 'amazon', 'amazon_unique_name', $unique_name ) );
	}

    /**
     * @return ISetting
     */
    public static function amazon_accounts() {
        return AmazonSettings::get( 'amazon_accounts' );
    }

	public static function get_amazon_account_by_name( $account_name ) {
		$accounts = AmazonSettings::amazon_accounts();
		$acc = array();
		foreach( $accounts as $account ) {
			if ( $account['amazon_unique_name'] == $account_name ) {
				$acc = $account;
				break;
			}
		}
		return $acc;
	}

	/**
	 * @return string
	 */
	public static function amazon_unique_name() {
		return AmazonSettings::get( 'amazon_unique_name' );
	}

	/**
	 * @param string $secret_access_key
	 */
	public static function set_amazon_secret_access_key( $secret_access_key ) {
		AmazonSettings::set( new Setting( 'amazon', 'amazon_secret_access_key', $secret_access_key ) );
	}

	/**
	 * @return string
	 */
	public static function amazon_secret_access_key() {
		return AmazonSettings::get( 'amazon_secret_access_key' );
	}

	/**
	 * @param string $access_key_id
	 */
	public static function set_amazon_access_key_id ($access_key_id ) {
		AmazonSettings::set( new Setting( 'amazon', 'amazon_access_key_id', $access_key_id ) );
	}

	/**
	 * @return string
	 */
	public static function amazon_access_key_id() {
		return AmazonSettings::get( 'amazon_access_key_id' );
	}

	/**
	 * @param string $affiliate_link
	 */
	public static function set_amazon_affiliate_link($affiliate_link ) {
		AmazonSettings::set( new Setting( 'amazon', 'amazon_affiliate_link', $affiliate_link ) );
	}

	/**
	 * @return string
	 */
	public static function amazon_affiliate_link() {
		return AmazonSettings::get( 'amazon_affiliate_link' );
	}
}

