<?php

namespace Taro\GeoTaxonomy\Admin;


use phpDocumentor\Transformer\Exception;
use Taro\Common\Pattern\Application;
use Taro\GeoTaxonomy\Models\Zip;


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
			add_action('wp_ajax_taro-geo-sync', array($this, 'sync'));
			add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
		}
	}

	/**
	 * Enqueue script
	 */
	public function enqueue_scripts(){
		wp_enqueue_script('taro-geo-admin', $this->assets.'/js/dist/setting.js', array('jquery-effects-highlight'), filemtime($this->root_dir.'/assets/js/dist/setting.js'), true);
		wp_localize_script('taro-geo-admin', 'TaroGeoVars', array(
			'loading' => $this->i18n->_('読み込み中...'),
		));
		wp_enqueue_style('taro-geo-admin', $this->assets.'/css/admin.css', null, filemtime($this->root_dir.'/assets/css/admin.css'));
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
			switch( $this->input->post('step') ){
				case 1:
					// Check temp dir
					$temp_dir = sys_get_temp_dir();
					if( !is_writable($temp_dir) ){
						throw new \Exception($this->i18n->s('一時ディレクトリに書き込みができませんでした。'), 500);
					}
					// Get zip
					$response = wp_remote_get($this->option['source']['url']);
					if( is_wp_error($response) ){
						throw new \Exception($response->get_error_message(), $response->get_error_code());
					}
					// Save data
					$zip_name = tempnam($temp_dir, 'taro-geo');
					file_put_contents($zip_name, $response['body']);
					if( !file_exists($zip_name) ){
						throw new \Exception($this->i18n->s('一時ファイルの保存に失敗しました。'), 500);
					}
					// Extract zip
					$csv = $zip_name.'.csv';
					$zip = zip_open($zip_name);
					if ($zip) {
						while ($zip_entry = zip_read($zip)) {
							$fp = fopen($csv, "w");
							if (zip_entry_open($zip, $zip_entry, "r")) {
								$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
								fwrite($fp,"$buf");
								zip_entry_close($zip_entry);
								fclose($fp);
							}
						}
						zip_close($zip);
					}else{
						throw new \Exception($this->i18n->s('Zipファイルの展開に失敗しました。'), 500);
					}

					if( !file_exists($csv) ){
						throw new \Exception($this->i18n->s('Zipファイルの展開に失敗しました。'), 500);
					}
					update_option('taro-geo-csv', $csv);
					$response = array(
						'next' => 2,
						'error' => false,
						'rows' => 0,
						'message' => $this->i18n->s('CSVファイルを取得しました……'),
					);
					break;
				case 2:
					// Current row
					$rows = $this->input->post('rows');
					$csv = get_option('taro-geo-csv');
					if( !file_exists($csv) || !($handle = new \SplFileObject($csv)) ){
						throw new \Exception($this->i18n->_('ファイルが存在しません。'), 403);
					}
					// Parse CSV
					$model = Zip::get_instance();
					$handle->seek($rows);
					// Iteration
					set_time_limit(0);
					$done = 0;
					while( !$handle->eof() ) {
						$row = $handle->fgetcsv();
						if ( count( $row ) > 7 ) {
							$row = array_map( function ( $cell ) {
								return mb_convert_kana( mb_convert_encoding( $cell, 'utf-8', 'sjis-win' ), 'KV', 'utf-8' );
							}, $row );
							list( $id, $short_zip, $zip, $pref_slug, $city_slug, $town_slug, $pref, $city, $town ) = $row;
							$model->add( $zip, $pref, $city, $town, $pref_slug, $city_slug, $town_slug );
						}
						$done ++;
						$rows ++;
						if ( $done >= 3000 ) {
							break;
						}
					}
					if( $handle->eof() ){
						unlink($csv);
						delete_option('taro-geo-csv');
					}
					$response = array(
						'next' => $handle->eof() ? 1 : 2,
						'error' => false,
						'rows' => $rows,
						'message' => $this->i18n->s('地域情報%d件のインポートに成功しました。', $rows),
					);

					break;
				default:
					throw new \Exception($this->i18n->_('不正な処理です。'), 403);
					break;
			}
		}catch ( \Exception $e ){
			$response = array(
				'next' => 1,
				'rows' => 0,
				'error' => $e->getCode(),
				'message' => $e->getMessage(),
			);
		}
		wp_send_json($response);
	}



	/**
	 * Sync Area Data
	 */
	public function sync(){
		try{
			if( !current_user_can('manage_options') || !$this->input->verify_nonce('taro-geo-sync') ){
				throw new \Exception($this->i18n->_('あなたには権限がありません。'), 403);
			}
			// Check zip total
			$zip = Zip::get_instance();
			$total = $zip->city_total();
			if( !$total ){
				throw new \Exception($this->i18n->_('市区町村が登録されていません'), 404);
			}
			switch( $this->input->post('step') ){
				case 1:
					// Count total
					$response = array(
						'next' => 2,
						'error' => false,
						'rows' => 0,
						'message' => $this->i18n->s('市区町村%d件を登録します……', $total),
					);
					break;
				case 2:
					// Current row
					$rows = $this->input->post('rows');
					$cities = $zip->get_cities($rows);
					if( $cities ){
						// Save prefecture
						$prefectures = array();
						foreach( $cities as $city ){
							if( !isset($prefectures[$city->prefecture]) ){
								$prefectures[$city->prefecture] = array(
									'description' => $city->prefecture_yomi,
								);
							}
						}
						// Check term exists
						foreach( $prefectures as $pref => $data){
							if( ($term = term_exists($pref, $this->taxonomy)) ){
								$prefectures[$pref]['term_id'] = $term['term_id'];
								$prefectures[$pref]['term_taxonomy_id'] = $term['term_taxonomy_id'];
							}else{
								$term = wp_insert_term($pref, $this->taxonomy, array(
									'description' => $data['description'],
								));
								if( !is_wp_error($term) ){
									$prefectures[$pref]['term_id'] = $term['term_id'];
									$prefectures[$pref]['term_taxonomy_id'] = $term['term_taxonomy_id'];
								}
							}
						}
						// O.K. Let's save cities
						foreach( $cities as $city ){
							$parent_id = $prefectures[$city->prefecture]['term_id'];
							if( !term_exists($city->city, $this->taxonomy, $parent_id) ){
								wp_insert_term($city->city, $this->taxonomy, array(
									'parent' => $parent_id,
									'description' => $city->city_yomi,
								));
							}
							$rows++;
						}
						$next = 2;
						$message = $this->i18n->s('市区町村%d件を同期しました……', $rows);
					}else{
						$next = 1;
						$message = $this->i18n->s('市区町村%d件の同期が完了しました。', $rows);
					}
					$response = array(
						'next' => $next,
						'error' => false,
						'rows' => $rows,
						'message' => $message,
					);

					break;
				default:
					throw new \Exception($this->i18n->_('不正な処理です。'), 403);
					break;
			}
		}catch ( \Exception $e ){
			$response = array(
				'next' => 1,
				'rows' => 0,
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
