<?php

namespace Taro\GeoTaxonomy\Ajax;


use Taro\Common\Pattern\Application;
use Taro\GeoTaxonomy\Helper\Address;
use Taro\GeoTaxonomy\Models\Point;

/**
 * Class PointSearch
 *
 * @package Taro\GeoTaxonomy\Ajax
 * @property-read Point $points
 */
class PointSearch extends Application
{

	/**
	 * Constructor
	 *
	 * @param array $arguments
	 */
	protected function __construct( $arguments = array() ) {

		// Add ajax point
		if( is_admin() && defined('DOING_AJAX') && DOING_AJAX ){
			add_action('wp_ajax_point_search', array($this, 'point_search'));
			add_action('wp_ajax_nopriv_point_search', array($this, 'point_search'));
		}
	}

	/**
	 * Ajax search
	 */
	public function point_search(){
		$json = array();
		foreach( get_posts(array(
			'post_type' => 'estate',
			'no' => $this->input->get('no'),
			'we' => $this->input->get('we'),
			'ea' => $this->input->get('ea'),
			'so' => $this->input->get('so'),
			'suppress_filters' => false,
		)) as $post){
			$address = new Address($post);
			$json[$post->ID] = array(
				'post_title' => $post->post_title,
				'zip' => $post->_zip,
				'address' => $address->prefecture,
				'point' => array(
					'lat' => $address->lat,
					'lng' => $address->lng
				),
			);
		}
		wp_send_json($json);
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return null|static
	 */
	public function __get( $name ) {
		switch( $name ){
			case 'points':
				return Point::get_instance();
				break;
			default:
				return parent::__get($name);
				break;
		}
	}


}
