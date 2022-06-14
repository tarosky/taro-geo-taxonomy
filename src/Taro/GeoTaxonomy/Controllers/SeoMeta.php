<?php

namespace Taro\GeoTaxonomy\Controllers;


use Taro\GeoTaxonomy\Helper\Address;
use Taro\GeoTaxonomy\Pattern\Application;

/**
 * SEO handler.
 */
class SeoMeta extends Application {

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $arguments = array() ) {
		// For Yoast SEO.
		add_filter( 'wpseo_schema_graph', [ $this, 'add_yoast_json' ] );
		// If yoast not exists, render json ld.
		add_action( 'wp_head', [ $this, 'add_json_ld' ], 99 );
	}

	/**
	 * Add JSON ld to yoast.
	 *
	 * @return array
	 */
	public function add_yoast_json( $pieces ) {
		if ( $this->should_render_json_ld() ) {
			$pieces[] = $this->get_json( get_queried_object() );
		}
		return $pieces;
	}

	/**
	 * Render JSON-LD
	 *
	 * @return void
	 */
	public function add_json_ld() {
		// Yoast exists?
		if ( defined( 'WPSEO_FILE' ) ) {
			return;
		}
		// Should render?
		if ( ! $this->should_render_json_ld() ) {
			return;
		}
		$json = $this->get_json( get_queried_object() );
		$json['@context'] = 'https://schema.org';
		printf( "<!-- Taro Geo Taxonomy -->\n<script type=\"application/ld+json\">\n%s\n</script>", json_encode( $json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
	}

	/**
	 * JSON LD.
	 *
	 * @param \WP_Post $post Post object.
	 * @return array
	 */
	public function get_json( $post ) {
		$json = [
			'@type' => $this->get_type( $post ),
			'name'  => get_the_title( $post ),
			'url'   => get_permalink( $post ),
		];
		// Address.
		$address = new Address( $post );
		$detail = $address->get();
		$json[ 'address' ] = apply_filters( 'taro_geo_taxonomy_json_ld_address', [
			'@type'           => 'PostalAddress',
			'streetAddress'   => trim( $detail['street'] . ' ' . $detail['building'] ),
			'addressLocality' => $detail['city'],
			'addressRegion'   => $detail['prefecture'],
			'postalCode'      => $detail['zip'],
			'addressCountry'  => apply_filters( 'taro_geo_taxonomy_json_ld_country', 'JP', $post )
		], $post );
		// Geo location.
		$lat = $address->lat;
		$lng = $address->lng;
		if ( $lat && $lng ) {
			$json[ 'geo' ] = [
				'@type'     => 'GeoCoordinates',
				'latitude'  => $lat,
				'longitude' => $lng,
			];
		}
		return $json;
	}

	/**
	 * Get type.
	 *
	 * @see https=>//schema.org/LocalBusiness
	 * @param \WP_Post $post Post object.
	 *
	 * @return string
	 */
	protected function get_type( $post ) {
		return apply_filters( 'taro_geo_taxonomy_json_ld_type', 'LocalBusiness', $post );
	}

	/**
	 * Should render JSON-LD?
	 *
	 * @return bool
	 */
	public function should_render_json_ld() {
		if ( ! is_singular() ) {
			return false;
		}
		return $this->is_supported( get_queried_object()->post_type );
	}
}
