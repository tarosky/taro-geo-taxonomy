<?php

namespace Taro\GeoTaxonomy\Admin;


use Taro\Common\Pattern\Application;


/**
 * Meta box for taxonomy
 *
 * @package Taro\GeoTaxonomy\Admin
 */
class MetaBox extends Application
{

	/**
	 * Constructor
	 *
	 * @param array $arguments
	 */
	protected function __construct( $arguments = array() ) {
		add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 10, 2);
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
		wp_enqueue_style('taro-geo-mb', $this->assets.'/css/edit-screen.css', array(), $this->version);
		wp_enqueue_script('taro-geo-mb', $this->assets.'/js/dist/edit-screen.js', array('jquery-effects-highlight', 'google-map'), $this->version, true);
	}

	/**
	 * Render meta boxes
	 *
	 * @param \WP_Post $post
	 */
	public function do_meta_box( \WP_Post $post ){
		$this->load_template('meta-box', array(
			'post' => $post
		));
	}

}
