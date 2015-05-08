<?php

namespace Taro\GeoTaxonomy\Admin;


use Taro\Common\Pattern\Application;
use Taro\GeoTaxonomy\Models\Zip;


/**
 * Meta box for taxonomy
 *
 * @package Taro\GeoTaxonomy\Admin
 * @property-read Zip $model
 */
class MetaBox extends Application
{

	/**
	 * Constructor
	 *
	 * @param array $arguments
	 */
	protected function __construct( $arguments = array() ) {
		add_action('admin_init', array($this, 'admin_init'));
		add_action('save_post', array($this, 'save_post'), 10, 2);
		add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 10, 2);
	}

	/**
	 * Register Ajax
	 */
	public function admin_init(){
		if( defined('DOING_AJAX') && DOING_AJAX ){
			add_action('wp_ajax_geo_token_input', array($this, 'geo_token_input'));
			add_action('wp_ajax_admin_zip_search', array($this, 'admin_zip_search'));
		}
	}

	/**
	 * Search token input
	 */
	public function geo_token_input(){
		$parent = $this->input->get('term_id');
		$q = $this->input->get('q');
		if( $parent < 1 || !$q ){
			wp_send_json(array());
		}
		$terms = array();
		foreach( $this->model->search_city($q, $parent) as $row ){
			$terms[] = array(
				'id' => (int) $row->term_id,
				'name' => sprintf("%s（%s）", $row->name, $row->description)
			);
		}
		wp_send_json($terms);
	}

	/**
	 * Search with Zip on admin panel
	 */
	public function admin_zip_search(){
		$zip = preg_replace('/[^0-9]/', '', ( $this->input->get('zip') ?: '' ));
		if( !preg_match('/^[0-9]{3,7}$/', $zip) || !($address = $this->model->search_from_zip($zip)) ){
			wp_send_json(array());
		}
		$json = array(
			'prefecture' => $address->prefecture,
		);
		$city = get_term_by('name', $address->city, $this->model->taxonomy);
		if( $city ){
			$json['city'] = array(
				'id'   => (int) $city->term_id,
				'name' => sprintf("%s（%s）", $city->name, $city->description),
			);
		}
		if( false === strpos($address->town, '以下に掲載') ){
			$json['street'] = $address->town;
		}
		wp_send_json($json);
	}

	/**
	 * Save post
	 *
	 * @param int $post_id
	 * @param \WP_Post $post
	 */
	public function save_post($post_id, \WP_Post $post){
		if( wp_is_post_autosave($post) || wp_is_post_revision($post) ){
			return;
		}
		if( $this->input->verify_nonce('taro_geo_save', '_taro_geo_nonce') ){
			$prefecture_id = $this->input->post('prefecture');
			$city_id = $this->input->post('city');
			$terms = array();
			if( '0' !== $prefecture_id && term_exists((int) $prefecture_id, $this->model->taxonomy, 0) ){
				$terms[] = (int)$prefecture_id;
				if( term_exists( (int)$city_id, $this->model->taxonomy, $prefecture_id) ){
					$terms[] = (int) $city_id;
				}
			}
			wp_set_object_terms($post->ID, $terms, $this->model->taxonomy);
			update_post_meta($post->ID, '_zip', $this->input->post('zip'));
			update_post_meta($post->ID, '_street', $this->input->post('street'));
			update_post_meta($post->ID, '_building', $this->input->post('building'));
		}
	}

	/**
	 * Register meta boxes
	 *
	 * @param string $post_type
	 * @param mixed|\WP_Post $post
	 */
	public function add_meta_boxes($post_type, $post){
		if( $this->is_supported($post_type) ){
			/**
			 * taro_geo_taxonomy_metabox_context
			 *
			 * @param array $context { 'context' => 'normal', 'priority' => 'high' }
			 */
			$context = apply_filters('taro_geo_taxonomy_metabox_context', array(
				'context' => 'normal',
				'priority' => 'high',
			));
			add_meta_box('taro-geo-taxonomy-box', $this->i18n->_('地域'), array($this, 'do_meta_box'), $post_type, $context['context'], $context['priority']);
			// Remove original Taxonomy
			remove_meta_box($this->taxonomy.'div', $post_type, 'side');
			// Add CSS and JS
			add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
		}
	}

	/**
	 * Enqueue Assets
	 */
	public function enqueue_assets(){
		wp_enqueue_style('taro-geo-mb', $this->assets.'/css/edit-screen.css', array('jquery-token-input'), $this->version);
		wp_enqueue_script('taro-geo-mb', $this->assets.'/js/dist/edit-screen.js', array('jquery-effects-highlight', 'jquery-token-input', 'google-map'), $this->version, true);
		wp_localize_script('taro-geo-mb', 'TaroGeo', array(
			'token' => admin_url('admin-ajax.php?action=geo_token_input'),
			'zip'   => admin_url('admin-ajax.php?action=admin_zip_search')
		));
	}

	/**
	 * Render meta boxes
	 *
	 * @param \WP_Post $post
	 */
	public function do_meta_box( \WP_Post $post ){
		wp_nonce_field('taro_geo_save', '_taro_geo_nonce', false);
		$this->load_template('meta-box', array(
			'post' => $post
		));
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch( $name ){
			case 'model':
				return Zip::get_instance();
				break;
			default:
				return parent::__get( $name );
				break;
		}
	}


}
