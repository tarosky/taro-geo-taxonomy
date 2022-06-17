<?php

namespace Taro\GeoTaxonomy;


use Taro\GeoTaxonomy\Admin\MetaBox;
use Taro\GeoTaxonomy\Admin\Setting;
use Taro\GeoTaxonomy\Ajax\PointSearch;
use Taro\GeoTaxonomy\Controllers\SeoMeta;
use Taro\GeoTaxonomy\Helper\Commands;
use Taro\GeoTaxonomy\Models\Point;
use Taro\GeoTaxonomy\Models\Zip;
use Taro\GeoTaxonomy\Pattern\Application;
use Taro\GeoTaxonomy\Rest\RestGeocoding;


/**
 * Boot strap plugin
 *
 * @package Taro\GeoTaxonomy
 */
class BootStrap extends Application {
	/**
	 * Constructor
	 *
	 * @param array $arguments
	 */
	protected function __construct( $arguments = array() ) {
		add_action( 'init', [ $this, 'init' ] );
		// Add point model
		Point::register();
		// SEO register.
		SeoMeta::get_instance();
		if ( is_admin() ) {
			// Enable settings page
			Setting::get_instance();
			// Enable Meta box
			MetaBox::get_instance();
			// Create tables
			Zip::register();
		}
		// REST API
		RestGeocoding::get_instance();
		// CLI Registration.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'taro-geo', Commands::class );
		}
	}

	/**
	 * Create taxonomy
	 */
	public function init() {
		// Create taxonomy
		if ( $this->taxonomy && $this->label ) {
			register_taxonomy( $this->taxonomy, $this->option['post_types'], array(
				'label'        => $this->label,
				'public'       => true,
				'hierarchical' => true,
				'rewrite'      => array(
					'slug'         => $this->taxonomy,
					'with_front'   => false,
					'hierarchical' => true,
				),
			) );
		}
		// Register google map
		$key = $this->google_api_key;
		wp_register_script( 'google-map', "//maps.googleapis.com/maps/api/js?key={$key}&sensor=true", array(), null, true );
		wp_register_script( 'geolonia-map', "https://cdn.geolonia.com/v1/embed?geolonia-api-key={$this->option['geolonia_key']}", array(), null, true );
		wp_register_script( 'jquery-token-input', $this->assets . '/vendor/jquery-tokeninput.min.js', array( 'jquery' ), '1.6.0', true );
		// Register assets.
		$json = $this->root_dir . '/wp-dependencies.json';
		if ( file_exists( $json ) ) {
			$assets = json_decode( file_get_contents( $json ), true );
			if ( $assets ) {
				foreach ( $assets as $asset ) {
					if ( ! $asset ) {
						continue;
					}
					$url = dirname( $this->assets ) . '/' . $asset['path'];
					switch ( $asset['ext'] ) {
						case 'js':
							wp_register_script( $asset['handle'], $url, $asset['deps'], $asset['hash'], $asset['footer'] );
							break;
						case 'css':
							wp_register_style( $asset['handle'], $url, $asset['deps'], $asset['hash'], $asset['media'] );
							break;
					}
				}
			}
		}
	}

}
