<?php

namespace Taro\GeoTaxonomy;


use Taro\GeoTaxonomy\Admin\MetaBox;
use Taro\GeoTaxonomy\Admin\Setting;
use Taro\Common\Pattern\Application;
use Taro\GeoTaxonomy\Models\Point;
use Taro\GeoTaxonomy\Models\Zip;


/**
 * Boot strap plugin
 *
 * @package Taro\GeoTaxonomy
 */
class BootStrap extends Application
{
	/**
	 * Constructor
	 *
	 * @param array $arguments
	 */
	protected function __construct( $arguments = array() ) {

		if( is_admin() ){
			// Enable settings page
			Setting::get_instance();
			// Enable Metabox
			MetaBox::get_instance();
			// Create tables
			Zip::register();
			Point::register();
		}
		add_action('init', array($this, 'init'));
	}

	/**
	 * Create taxonomy
	 */
	public function init(){
		// Create taxonomy
		if( $this->taxonomy && $this->label ){
			register_taxonomy($this->taxonomy, $this->option['post_types'], array(
				'label' => $this->label,
				'public' => true,
				'hierarchical' => true,
			));
		}
		// Register google map
		$key = defined('TAROGEO_GOOGLE_KEY') ? TAROGEO_GOOGLE_KEY : '';
		wp_register_script('google-map', "//maps.googleapis.com/maps/api/js?key={$key}&sensor=true", array(), null, true);
	}

}
