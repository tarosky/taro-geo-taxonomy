<?php

namespace Taro\GeoTaxonomy\Helper;


use cli\Table;
use Taro\GeoTaxonomy\Controllers\GeocodeUpdater;

/**
 * CLI utility for Taro GeoTaxonomy
 */
class Commands extends \WP_CLI_Command {

	/**
	 * @param $args
	 *
	 * @synopsis <limit>
	 * @return void
	 */
	public function update_geocode( $args ) {
		list( $limit ) = $args;
		$result        = GeocodeUpdater::get_instance()->update_google_geocode( $limit );
		if ( is_wp_error( $result ) ) {
			\WP_CLI::error( $result->get_error_message() );
		}
		// translators: %d is updated count.
		\WP_CLI::success( sprintf( __( '%d件の位置情報を更新しました。', 'taro-geo-tax' ), $result ) );
	}

	/**
	 * Get lat lang from text.
	 *
	 *
	 * @synopsis <address>
	 * @param array $args   Arguments.
	 * @param array $option Options.
	 * @return void
	 */
	public function get_geocode( $args, $option ) {
		list( $address ) = $args;
		$result = GeocodeUpdater::get_instance()->geocode( $address );
		if ( is_wp_error( $result ) ) {
			\WP_CLI::error( $result->get_error_message() );
		}
		$table = new Table();
		$table->setHeaders( [ __( '経度', 'taro-geo-tax' ), __( '緯度', 'taro-geo-tax' ) ] );
		$table->addRow( $result );
		$table->display();
	}
}
