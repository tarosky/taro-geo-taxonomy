<?php
/**
 * Plugin Name: Taro Geo Taxonomy
 * Plugin URI: https://github.com/tarosky/taro-geo-taxonomy
 * Description: The WordPress plugin which creates
 * Author: Tarosky INC.
 * Version: 1.1.2
 * Author URI: https://tarosky.co.jp
 * License: MIT
 */

require __DIR__.'/vendor/autoload.php';

if ( class_exists( 'Taro\\GeoTaxonomy\\BootStrap' ) ) {
	Taro\GeoTaxonomy\BootStrap::get_instance();
}
