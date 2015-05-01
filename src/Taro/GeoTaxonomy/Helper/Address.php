<?php

namespace Taro\GeoTaxonomy\Helper;


use Taro\GeoTaxonomy\Models\Point;


/**
 * Address helper
 *
 * @package Taro\GeoTaxonomy\Helper
 * @property-read Point $model
 * @property-read string $zip
 * @property-read \stdClass|null $prefecture
 * @property-read \stdClass|null $city
 * @property-read string $address
 * @property-read string $building
 * @property-read float|false $lat
 * @property-read float|false $lng
 */
class Address
{
	/**
	 * @var \WP_Post
	 */
	public $post = null;


	public function __construct($post = null){
		$this->post = get_post($post);
	}

	/**
	 * Get all prefecture
	 *
	 * @return array
	 */
	public function get_prefectures(){
		$terms = get_terms($this->model->taxonomy, array(
			'parent' => 0,
			'hide_empty' => false,
			'order' => 'ASC',
			'orderby' => 'id',
		));
		return is_wp_error($terms) ? array() : $terms;
	}

	public function get_city_of($prefecture){

	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return array|bool|float|mixed|null|\stdClass
	 */
	public function __get( $name ){
		switch( $name ){
			case 'model':
				return Point::get_instance();
				break;
			case 'zip':
			case 'address':
			case 'building':
				return get_post_meta($this->post->ID, '_'.$name, true);
				break;
			case 'prefecture':
			case 'city':
				break;
			case 'lat':
			case 'lng':
				if( ($point = $this->latlng) ){
					return (float) $point->{$name};
				}else{
					return false;
				}
				break;
			case 'latlng':
				return Point::get_instance()->get_points('post_address', $this->post->ID, true);
				break;
			default:
				return null;
				break;
		}
	}

}
