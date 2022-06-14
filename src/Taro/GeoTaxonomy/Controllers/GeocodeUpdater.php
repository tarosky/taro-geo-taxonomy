<?php

namespace Taro\GeoTaxonomy\Controllers;

use Taro\GeoTaxonomy\Models\Point;
use Taro\GeoTaxonomy\Pattern\Application;

/**
 * Update Geolocation
 */
class GeocodeUpdater extends Application {

	const UPDATE_EVENT = 'taro_geto_taxonomy_update_geo';

	/**
	 * Update geocoding
	 *
	 * @return int
	 */
	protected function per_hour() {
		return max( 1, apply_filters( 'taro_geo_taxonomy_update_limit', 10 ) );
	}

	/**
	 * Get geocode max limit.
	 *
	 * @return int
	 */
	protected function google_cache_limit() {
		return (int) apply_filters( 'taro_geo_taxonomy_geocode_cache_time', 60 * 60 * 24 * 30 );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function __construct( $arguments = array() ) {
		add_action( 'init', [ $this, 'register_cron' ] );
		add_action( self::UPDATE_EVENT, [ $this, 'do_cron' ] );
	}

	/**
	 * Register cron
	 *
	 * @return void
	 */
	public function register_cron() {
		if ( wp_next_scheduled( self::UPDATE_EVENT ) ) {
			return;
		}
		wp_schedule_event( time(), 'hourly', self::UPDATE_EVENT );
	}

	/**
	 * @return void
	 */
	public function do_cron() {

	}

	/**
	 * Update google geocode.
	 *
	 * @return int|\WP_Error
	 */
	public function update_google_geocode( $limit ) {
		$offset = $this->google_cache_limit();
		$points = Point::get_instance()->get_points_to_be_refreshed( [
			'limit'  => $limit,
			'offset' => $offset,
		] );
		$error   = new \WP_Error();
		$updated = 0;
		foreach ( $points as $point ) {
			// Do update.
		}
		return empty( $error->get_error_messages() ) ? $updated : $error;
	}
}
