<?php

namespace Taro\GeoTaxonomy\Pattern;


/**
 * REST API pattern.
 */
abstract class RestApiPattern extends Application {

	protected $namespace = 'taro-geo/v1';

	/**
	 * Get route.
	 *
	 * @return string
	 */
	abstract protected function route();

	/**
	 * HTTP methods to allow.
	 *
	 * @return string[]
	 */
	protected function methods() {
		return [ 'GET' ];
	}

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $arguments = array() ) {
		add_action( 'rest_api_init', [ $this, 'register_rest' ] );
	}

	/**
	 *
	 *
	 * @param string $method
	 * @return array
	 */
	protected function get_arguments( $method ) {
		return [];
	}

	/**
	 * Register API.
	 *
	 * @return void
	 */
	public function register_rest() {
		$methods  = (array) $this->methods();
		$handlers = [];
		foreach ( $methods as $method ) {
			$handlers[] = [
				'methods'             => $method,
				'args'                => $this->get_arguments( $method ),
				'callback'            => [ $this, 'callback' ],
				'permission_callback' => [ $this, 'permission_callback' ],
			];
		}
		register_rest_route( $this->namespace, $this->route(), $handlers );
	}

	/**
	 * Callback.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_Error|\WP_REST_Response
	 */
	abstract public function callback( \WP_REST_Request $request );

	/**
	 * Permission handler.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool|\WP_Error
	 */
	public function permission_callback( $request ) {
		return true;
	}
}
