<?php

namespace Taro\GeoTaxonomy\Pattern;


use Taro\GeoTaxonomy\Utility\Input;
use Taro\GeoTaxonomy\Utility\Internationalization;


/**
 * Application class
 *
 * @package Taro\GeoTaxonomy\Pattern
 * @property-read array $option
 * @property-read string $google_api_key
 * @property-read string $taxonomy
 * @property-read string $label
 * @property-read \Taro\GeoTaxonomy\Utility\Input $input
 * @property-read \Taro\GeoTaxonomy\Utility\Internationalization $i18n
 * @property-read string $root_dir
 * @property-read string $assets
 */
abstract class Application extends Singleton {
	/**
	 * Current plugin version
	 *
	 * @var string
	 */
	protected $version = '1.0';

	/**
	 * Default option value
	 *
	 * @return array
	 */
	private function get_default_option() {
		return array(
			'taxonomy'   => 'area',
			'label'      => __( '地域', 'taro-geo-tax' ),
			'source'     => array(
				'label'       => '日本',
				'url'         => 'http://www.post.japanpost.jp/zipcode/dl/kogaki/zip/ken_all.zip',
				// translators: %s is url.
				'description' => sprintf( __( '郵便局が公開している<a href="%s">郵便番号データ</a>を元にしています。', 'taro-geo-tax' ), 'http://www.post.japanpost.jp/zipcode/dl/kogaki-zip.html' ),
			),
			'post_types' => array(),
			'api_key'    => '',
		);
	}

	/**
	 * Load template file
	 *
	 * @param string $template
	 * @param array $args will be extract.
	 */
	public function load_template( $template, $args = array() ) {
		if ( false === strpos( $template, '.php' ) ) {
			$template .= '.php';
		}
		$path = $this->root_dir . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template;
		if ( file_exists( $path ) ) {
			if ( $args ) {
				// phpcs:ignore
				extract( $args );
			}
			include $path;
		} else {
			// translators: %s is file path.
			trigger_error( sprintf( __( 'テンプレートファイル%sは存在しません。', 'taro-geo-tax' ), $path ), E_USER_WARNING );
		}
	}

	/**
	 * Detect if post type is supported
	 *
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public function is_supported( $post_type ) {
		return in_array( $post_type, $this->option['post_types'], true );
	}

	/**
	 * Getter
	 *
	 * @param string $name Property name.
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'option':
				$saved_option = get_option( 'taro-geo-setting', array() );
				foreach ( $this->get_default_option() as $key => $value ) {
					if ( ! isset( $saved_option[ $key ] ) ) {
						$saved_option[ $key ] = $value;
					}
				}

				return $saved_option;
			case 'google_api_key':
				$key = defined( 'TAROGEO_GOOGLE_KEY' ) ? TAROGEO_GOOGLE_KEY : $this->option['api_key'];
				return $key;
			case 'label':
			case 'taxonomy':
				return $this->option[ $name ];
			case 'input':
				return Input::get_instance();
				break;
			case 'i18n':
				return Internationalization::get_instance();
			case 'root_dir':
				return dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) );
			case 'assets':
				return untrailingslashit( plugin_dir_url( $this->root_dir . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'index.css' ) );
			default:
				return null;
		}
	}
}
