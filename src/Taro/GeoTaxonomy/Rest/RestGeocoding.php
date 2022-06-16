<?php

namespace Taro\GeoTaxonomy\Rest;


use Taro\GeoTaxonomy\Controllers\GeocodeUpdater;
use Taro\GeoTaxonomy\Pattern\RestApiPattern;

/**
 * Geocoding API.
 */
class RestGeocoding extends RestApiPattern {

	/**
	 * {@inheritdoc}
	 */
	protected function route() {
		return 'geocoding';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_arguments( $method ) {
		return [
			'text' => [
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => function( $var ) {
					return ! empty( $var );
				},
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function callback( \WP_REST_Request $request ) {
		$text     = $request->get_param( 'text' );
		$geocoded = GeocodeUpdater::get_instance()->geocode( $text );
		if ( is_wp_error( $geocoded ) ) {
			return $geocoded;
		}
		if ( empty( $geocoded ) ) {
			return new \WP_Error( 'geocoding_error', __( '該当する住所は見つかりませんでした。', 'taro-geo-tax' ), [
				'status' => 404,
			] );
		}
		list( $lng, $lat ) = $geocoded;
		return new \WP_REST_Response( [
			'lat' => $lat,
			'lng' => $lng,
		] );
	}


	/**
	 * {@inheritdoc}
	 */
	public function permission_callback( $request ) {
		return current_user_can( 'edit_posts' );
	}
}
