<?php

namespace Taro\Common\Utility;


use Taro\Common\Pattern\Singleton;

/**
 * i18n class
 *
 * @package Taro\GeoTaxonomy\Utility
 */
class Internationalization extends Singleton
{

	const DOMAIN = 'taro-geo-tax';

	/**
	 * Short hand for _e
	 *
	 * @param string $string
	 */
	public function e($string){
		echo $this->_($string);
	}

	/**
	 * Short hand for __
	 *
	 * @param string $string
	 *
	 * @return string|void
	 */
	public function _($string){
		return __($string, self::DOMAIN);
	}

	/**
	 * Shorthand for sprintf + __
	 *
	 * @param string $string
	 *
	 * @return string|void
	 */
	public function s($string){
		$args = func_get_args();
		if( 2 > count($args) ){
			return $this->_($string);
		}else{
			$args[0] = $this->_($string);
			return call_user_func_array('sprintf', $args);
		}
	}

	/**
	 * Short hand for printf + __
	 *
	 * @param string $string
	 * @param bool $escape If true, esc_html will be apply.
	 */
	public function p($string, $escape = false){
		$str = call_user_func_array(array($this, 's'), func_get_args());
		if( $escape ){
			$str = esc_html($str);
		}
		echo $str;
	}

}
