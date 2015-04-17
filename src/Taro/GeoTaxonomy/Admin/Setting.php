<?php

namespace Taro\GeoTaxonomy\Admin;


use phpDocumentor\Transformer\Exception;
use Taro\Common\Pattern\Application;


/**
 * Setting screen
 *
 * @package Taro\GeoTaxonomy\Admin
 */
class Setting extends Application
{

	/**
	 * Construct
	 *
	 * @param array $arguments
	 */
	protected function __construct( $arguments = array() ) {
		if( is_admin() ){
			add_action('admin_menu', array($this, 'admin_menu'));
			add_action('admin_init', array($this, 'admin_init'));
			add_action('wp_ajax_taro-geo-import', array($this, 'import'));
		}
	}

	/**
	 * Create admin menu
	 */
	public function admin_menu(){
		add_options_page(
			$this->i18n->s('Taro Geo Taxonomy 設定ページ'),
			$this->i18n->s('Geo Taxonomy'),
			'manage_options', 'taro-geo-taxonomy',
			array($this, 'render'));
	}

	/**
	 * Get available post types
	 *
	 * @return array
	 */
	public function get_post_types(){
		$post_types = get_post_types(array(
			'show_ui' => true,
		), 'objects');
		return $post_types;
	}

	/**
	 * Save settings
	 */
	public function admin_init(){
		if( $this->input->verify_nonce('taro-geo-taxonomy') && current_user_can('manage_options')){
			// Save setting
			$option = $this->option;
			$option = array_merge($option, array(
				'post_types' => (array) $this->input->post('post_types'),
				'taxonomy' => (string) $this->input->post('taxonomy-name'),
				'label' => (string) $this->input->post('taxonomy-label'),
			));
			update_option('taro-geo-setting', $option);
			$message = $this->i18n->s('設定が更新されました。');
			add_action('admin_notices', function() use ($message){
				printf('<div class="updated"><p>%s</p></div>', $message);
			});
		}
	}

	/**
	 * Import area data
	 */
	public function import(){
		try{
			if( !current_user_can('manage_options') || !$this->input->verify_nonce('taro-geo-import') ){
				throw new \Exception($this->i18n->_('あなたには権限がありません。'), 403);
			}
			$response = array(
				'error' => false,
				'message' => $this->i18n->_('地域情報のインポートに成功しました。'),
			);
		}catch ( \Exception $e ){
			$response = array(
				'error' => $e->getCode(),
				'message' => $e->getMessage(),
			);
		}
		wp_send_json($response);
	}

	/**
	 * Render screen
	 */
	public function render(){
		$this->load_template('setting');
	}
}
