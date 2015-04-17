<?php

namespace Taro\Common\Pattern;


/**
 * Singleton pattern
 *
 * @package Taro\GeoTaxonomy\Pattern
 */
abstract class Singleton
{
	/**
	 * Instance holder
	 *
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Constructor
	 *
	 * @param array $arguments
	 */
	protected function __construct( $arguments = array() ) {
		// Override if required
	}

	/**
	 * Get instance
	 *
	 * @param array $arguments
	 *
	 * @return static
	 */
	final public static function get_instance( $arguments = array() ) {
		$class_name = get_called_class();
		if ( ! isset( self::$instances[ $class_name ] ) ) {
			self::$instances[ $class_name ] = new $class_name( $arguments );
		}

		return self::$instances[ $class_name ];
	}
}
