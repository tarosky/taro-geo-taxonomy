<?php

namespace Taro\Common\Pattern;


use Taro\Common\Utility\Input;
use Taro\Common\Utility\Internationalization;


/**
 * Application class
 *
 * @package Taro\GeoTaxonomy\Pattern
 * @property-read array $option
 * @property-read string $taxonomy
 * @property-read string $label
 * @property-read Input $input
 * @property-read Internationalization $i18n
 * @property-read string $root_dir
 * @property-read string $assets
 */
abstract class Application extends Singleton
{

	/**
	 * Default option value
	 *
	 * @return array
	 */
	private function get_default_option(){
		return array(
			'taxonomy' => 'area',
			'label'    => $this->i18n->_('地域'),
			'source' => array(
				'label' => '日本',
				'url' => 'http://www.post.japanpost.jp/zipcode/dl/kogaki/zip/ken_all.zip',
				'description' => $this->i18n->s('郵便局が公開している<a href="%s">郵便番号データ</a>を元にしています。', 'http://www.post.japanpost.jp/zipcode/dl/kogaki-zip.html'),
			),
			'post_types' => array(),
		);
	}

	/**
	 * Load template file
	 *
	 * @param string $template
	 */
	public function load_template($template){
		if( false === strpos($template, '.php') ){
			$template .= '.php';
		}
		$path = $this->root_dir.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template;
		if( file_exists($path) ){
			include $path;
		}else{
			trigger_error($this->i18n->s('テンプレートファイル%sは存在しません。', $path), E_USER_WARNING);
		}
	}

	/**
	 * Detect if post type is supported
	 *
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public function is_supported($post_type){
		return false !== array_search($post_type, $this->option['post_types']);
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return null|static
	 */
	public function __get($name){
		switch( $name ){
			case 'option':
				$saved_option = get_option('taro-geo-setting', array());
				foreach( $this->get_default_option() as $key => $value ){
					if( !isset($saved_option[ $key ]) ){
						$saved_option[ $key ] = $value;
					}
				}
				return $saved_option;
				break;
			case 'label':
			case 'taxonomy':
				return $this->option[$name];
				break;
			case 'input':
				return Input::get_instance();
				break;
			case 'i18n':
				return Internationalization::get_instance();
				break;
			case 'root_dir':
				return dirname(dirname(dirname(dirname(dirname(__FILE__)))));
				break;
			case 'assets':
				return untrailingslashit(plugin_dir_url($this->root_dir.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'index.css'));
				break;
			default:
				return null;
				break;
		}
	}
}
