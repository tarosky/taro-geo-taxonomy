<?php

namespace Taro\GeoTaxonomy\Controllers;

use Aws\CognitoIdentity\CognitoIdentityClient;
use Aws\CognitoIdentity\CognitoIdentityProvider;
use Aws\Credentials\Credentials;
use Aws\LocationService\LocationServiceClient;
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
		$offset  = $this->google_cache_limit();
		$points  = Point::get_instance()->get_points_to_be_refreshed( [
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

	/**
	 * Convert address.
	 *
	 * @param string $address Address text.
	 * @return float[]|\WP_Error
	 */
	public function geocode( $address ) {
		try {
			$client = $this->get_location_client();
			$result = $client->searchPlaceIndexForText( [
				'Text'            => $address,
				'IndexName'       => $this->option['aws_index_name'],
				'FilterCountries' => [ $this->option['country'] ],
			] );
			if ( empty( $result['Results'] ) ) {
				return [];
			}
			foreach ( $result['Results'] as $latlng ) {
				return $latlng['Place']['Geometry']['Point'];
			}
		} catch ( \Exception $e ) {
			return new \WP_Error( 'geocode_error', '[' . $e->getCode() . ']' . $e->getMessage() );
		}
	}

	/**
	 * Get location service client.
	 *
	 * @return LocationServiceClient
	 */
	protected function get_location_client() {
		$region = apply_filters( 'taro_get_taxonomy_aws_region', 'ap-northeast-1' );
		$setting = [
			'version' => 'latest',
			'region'  => $region,
		];
		// If no credentials provided,
		// try to use default credentials.
		if ( $this->option['aws_access_secret'] && $this->option['aws_access_key'] ) {
			$setting['credentials'] = new Credentials( $this->option['aws_access_key'], $this->option['aws_access_secret'] );
		}
		$setting = apply_filters( 'taro_geo_taxonomy_aws_credentials', $setting, 'local_service_client' );
		return new LocationServiceClient( $setting );
	}
}
