<?php
declare( strict_types=1 ); // must be first line


namespace kdaviesnz\amazon;

class Option implements IOption{

	public static function getOption( $optionName ) {
		// for now we're just getting the option from config
		global $options;
		return isset( $options[$optionName] ) ? $options[$optionName] : false;
	}

}