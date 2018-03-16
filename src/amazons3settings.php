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
class AmazonS3Settings implements ISettings
{

    public static function verify() {
        return true;
    }

    /**
     * @param string $name
     * @return ISetting
     */
    public static function get( $name ) {
        $option = Option::getOption($name);
        return new Setting( 'amazons3', $name, $option );
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
            'amazon_s3region' => AmazonS3Settings::amazon_unique_name()->value()?AmazonSettings::amazon_unique_name()->value():"",
            'amazon_s3key' => AmazonS3Settings::amazon_secret_access_key()->value()?AmazonSettings::amazon_secret_access_key()->value():"",
            'amazon_s3secret' => AmazonS3Settings::amazon_access_key_id()->value()?AmazonSettings::amazon_access_key_id()->value():"",
        );
    }

    public static function reset() {
        delete_option( 'amazon_s3region' );
        delete_option( 'amazon_s3key' );
        delete_option( 'amazon_s3secret' );
        return true;
    }

    /**
     * @param array $values
     * @return bool
     */
    public static function save( $values )
    {

        if ( isset( $values['amazon_s3key'] ) && $values['amazon_s3key'] ) {

            AmazonS3Settings::set_amazon_s3key( $values['amazon_s3key'] );
            AmazonS3Settings::set_amazon_s3region( $values['amazon_s3region'] );
            AmazonS3Settings::set_amazon_s3secret( $values['amazon_s3secret'] );

        }

        return true;
    }

    /**
     * @param string $s3region
     */
    public static function set_amazon_s3region($s3region ) {
        AmazonSettings::set( new Setting( 'amazon', 'amazon_s3region', $s3region ) );
    }

    /**
     * @return string
     */
    public static function amazon_s3region() {
        return AmazonSettings::get( 'amazon_s3region' );
    }

    /**
     * @param string $s3key
     */
    public static function set_amazon_s3key( $s3key ) {
        AmazonSettings::set( new Setting( 'amazon', 'amazon_s3key', $s3key ) );
    }

    /**
     * @return string
     */
    public static function amazon_s3key() {
        return AmazonSettings::get( 'amazon_s3key' );
    }

    /**
     * @param string $access_key_id
     */
    public static function set_amazon_s3secret ($s3secret ) {
        AmazonSettings::set( new Setting( 'amazon', 'amazon_s3secret', $s3secret ) );
    }

    /**
     * @return string
     */
    public static function amazon_s3secret() {
        return AmazonSettings::get( 'amazon_s3secret' );
    }

}

