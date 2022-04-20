<?php
namespace Taro\GeoTaxonomy\Utility;


use Taro\GeoTaxonomy\Pattern\Singleton;
use function wp_verify_nonce;


/**
 * Input Class
 *
 * @package Taro\GeoTaxonomy\Utility
 */
class Input extends Singleton
{

	/**
	 * Get $_GET
	 *
	 * @param string $key
	 *
	 * @return string|null
	 */
	public function get($key) {
		return isset($_GET[ $key ]) ? $_GET[ $key ] : null;
	}

	/**
	 * Get $_POST
	 *
	 * @param string $key
	 *
	 * @return string|null
	 */
	public function post($key) {
		return isset($_POST[ $key ]) ? $_POST[ $key ] : null;
	}

	/**
	 * Get $_REQUEST
	 *
	 * @param string $key
	 *
	 * @return string|null
	 */
	public function request($key) {
		return isset($_REQUEST[ $key ]) ? $_REQUEST[ $key ] : null;
	}

	/**
	 * Test nonce
	 *
	 * @param string $action
	 * @param string $key
	 *
	 * @return bool
	 */
	public function verify_nonce($action, $key = '_wpnonce') {
		return $this->request($key) && wp_verify_nonce( $this->request( $key ), $action );
	}

}
