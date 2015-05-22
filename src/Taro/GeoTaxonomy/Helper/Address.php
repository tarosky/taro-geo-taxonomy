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
 * @property-read string $street
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

	/**
	 * @var array|null
	 */
	public $terms = null;

	/**
	 * @param null $post
	 */
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

	/**
	 * Get city list from prefecture
	 *
	 * @param string|int|\stdClass $prefecture
	 *
	 * @return array
	 */
	public function get_city_of($prefecture){
		if( is_numeric($prefecture) ){
			$pref_id = $prefecture;
		}elseif( is_object($prefecture) && isset($prefecture->term_id) ){
			$pref_id = $prefecture->term_id;
		}else{
			$prefecture = get_term($prefecture, $this->model->taxonomy);
			if( !$prefecture || is_wp_error($prefecture) ){
				return array();
			}
			$pref_id = $prefecture->term_id;
		}
		$cities = get_terms($this->model->taxonomy, array(
			'parent' => $pref_id,
			'hide_empty' => false,
			'order' => 'ASC',
			'orderby' => 'id'
		));
		return is_wp_error($cities) ? array() : $cities;
	}

	/**
	 * Get address
	 *
	 * @param bool $in_array
	 *
	 * @return array|string
	 */
	public function get($in_array = true){
		$address = array_map(function($var){
			if( isset($var->name) ){
				return $var->name;
			}else{
				return $var ?: '';
			}
		}, array(
			'prefecture' => $this->prefecture,
			'city'       => $this->city,
			'street'     => $this->street,
			'building'   => $this->building
		));
		return $in_array ? $address : trim(implode(' ', $address));
	}

	/**
	 * Print address
	 */
	public function the_address(){
		echo $this->get(false);
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
			case 'street':
			case 'building':
				return get_post_meta($this->post->ID, '_'.$name, true);
				break;
			case 'prefecture':
			case 'city':
				if( is_null($this->terms) ){
					$this->terms = get_the_terms($this->post, $this->model->taxonomy);
				}
				if( $this->terms && !is_wp_error($this->terms) ){
					foreach( $this->terms as $term ){
						if( 'prefecture' === $name ){
							if( 0 == $term->parent ){
								return $term;
							}
						}else{
							if( 0 != $term->parent ){
								return $term;
							}
						}
					}
				}
				return (object)array(
					'term_id' => 0,
					'term_taxonomy_id' => 0,
					'parent' => 0,
					'description' => '',
					'name' => '',
					'slug' => '',
				);
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
				return Point::get_instance()->get_point('post_address', $this->post->ID);
				break;
			default:
				return null;
				break;
		}
	}
}
