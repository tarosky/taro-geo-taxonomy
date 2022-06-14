<?php

namespace Taro\GeoTaxonomy\Utility;


use Taro\GeoTaxonomy\Pattern\Singleton;

/**
 * i18n class
 *
 * @package Taro\GeoTaxonomy\Utility
 * @deprecated
 *
 */
class Internationalization extends Singleton {


	const DOMAIN = 'taro-geo-tax';

	/**
	 * Short hand for _e
	 *
	 * @deprecated
	 * @param string $string
	 */
	public function e( $string ) {
		echo $this->_( $string );
	}

	/**
	 * Short hand for __
	 *
	 * @deprecated
	 * @param string $string
	 *
	 * @return string|void
	 */
	public function _( $string ) {
		// phpcs:ignore
		return __( $string, self::DOMAIN );
	}

	/**
	 * Shorthand for sprintf + __
	 *
	 * @deprecated
	 * @param string $string
	 *
	 * @return string|void
	 */
	public function s( $string ) {
		$args = func_get_args();
		if ( 2 > count( $args ) ) {
			return $this->_( $string );
		} else {
			$args[0] = $this->_( $string );
			return call_user_func_array( 'sprintf', $args );
		}
	}

	/**
	 * Short hand for printf + __
	 *
	 * @deprecated
	 * @param string $string
	 * @param bool $escape If true, esc_html will be apply.
	 */
	public function p( $string, $escape = false ) {
		$str = call_user_func_array( array( $this, 's' ), func_get_args() );
		if ( $escape ) {
			$str = esc_html( $str );
		}
		echo $str;
	}

}
