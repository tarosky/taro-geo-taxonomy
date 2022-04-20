<?php

namespace Taro\GeoTaxonomy\Models;


use Taro\GeoTaxonomy\Pattern\Model;

/**
 * Point class
 *
 * @package Taro\GeoTaxonomy\Models
 */
class Point extends Model
{

	protected $name = 'points';

	protected $version = '1.0';

	/**
	 * Constructor
	 *
	 * @param array $arguments
	 */
	protected function __construct( $arguments = array() ) {
		add_filter('query_vars', array($this, 'query_vars'));
		add_action('posts_join', array($this, 'posts_join'), 10, 2);
		add_action('posts_where', array($this, 'posts_where'), 10, 2);
	}

	/**
	 * Add query vars
	 *
	 * @param array $vars
	 *
	 * @return array
	 */
	public function query_vars($vars){
		return array_merge($vars, array(
			'no', 'so', 'ea', 'we'
		));
	}

	/**
	 * Detect if current query is valid
	 *
	 * @param \WP_Query $wp_query
	 *
	 * @return bool
	 */
	protected function is_valid_query( \WP_Query $wp_query ){
		return (
			$wp_query->get('no') && $wp_query->get('so') && $wp_query->get('ea') && $wp_query->get('we')
		);
	}

	/**
	 * Join with points
	 *
	 * @param string $join
	 * @param \WP_Query $wp_query
	 *
	 * @return mixed
	 */
	public function posts_join($join, \WP_Query $wp_query){
		if( $this->is_valid_query($wp_query) ){
			$join .= <<<SQL
			LEFT JOIN {$this->table} AS points
			ON points.point_key = 'post_address' AND {$this->db->posts}.ID = points.object_id
SQL;
		}
		return $join;
	}

	/**
	 * Where with point
	 *
	 * @param string $where
	 * @param \WP_Query $wp_query
	 *
	 * @return mixed
	 */
	public function posts_where($where, \WP_Query $wp_query){
		if( $this->is_valid_query($wp_query) ){
			$query = <<<SQL
			AND MBRContains(GeomFromText(%s), points.latlng)
SQL;
			$where .= $this->db->prepare($query, sprintf('LINESTRING(%s %s, %s %s)',
				$wp_query->get('ea'), $wp_query->get('no'),
				$wp_query->get('we'), $wp_query->get('so')
				));
		}
		return $where;
	}


	/**
	 * Create points table
	 *
	 * @return string
	 */
	protected function create_sql() {
		return <<<SQL
			CREATE TABLE `{$this->table}` (
				point_id    BIGINT NOT NULL AUTO_INCREMENT,
				point_key VARCHAR(48) NOT NULL,
				object_id   BIGINT NOT NULL,
				latlng      GEOMETRY NOT NULL,
				point_name  VARCHAR(256) NOT NULL,
				point_desc TEXT NOT NULL,
				PRIMARY KEY (point_id),
				INDEX from_key (point_key, object_id),
				INDEX from_id (object_id),
				INDEX from_name (point_key, point_name(9)),
				SPATIAL KEY (latlng)
			) ENGINE = MyISAM DEFAULT CHARSET utf8
SQL;
	}

	/**
	 * Add GEOM data
	 *
	 * @param string $key
	 * @param int $object_id
	 * @param float $lat
	 * @param float $lng
	 * @param string $name
	 * @param string $desc
	 *
	 * @return bool
	 */
	public function add_point($key, $object_id, $lat, $lng, $name = '', $desc = ''){
		$query = <<<SQL
			INSERT INTO {$this->table} (
				point_key, object_id, latlng, point_name, point_desc
			) VALUES (
				%s, %d, GeomFromText(%s), %s, %s
			)
SQL;
		return (bool) $this->query($query, $key, $object_id, "POINT({$lng} {$lat})", $name, $desc);
	}

	/**
	 * Update points
	 *
	 * @param string $key
	 * @param int $object_id
	 * @param float $lat
	 * @param float $lng
	 * @param string $name
	 * @param string $desc
	 *
	 * @return int
	 */
	public function update_points($key, $object_id, $lat, $lng, $name = '', $desc = ''){
		$query = <<<SQL
			UPDATE {$this->table}
			SET latlng = GeomFromText(%s),
				point_name = %s,
				point_desc = %s
			WHERE point_key = %s AND object_id = %d
SQL;
		return $this->query($query, "POINT({$lng} {$lat})",
			$name, $desc, $key, $object_id);
	}

	/**
	 * Update point by id
	 *
	 * @param int $point_id
	 * @param float $lat
	 * @param float $lng
	 * @param string $name
	 * @param string $desc
	 *
	 * @return int
	 */
	public function update_point($point_id, $lat, $lng, $name = '', $desc = ''){
		$query = <<<SQL
			UPDATE {$this->table}
			SET latlng = GeomFromText(%s),
			    point_name = %s,
			    point_desc = %s
			WHERE point_id = %d
SQL;
		return $this->query($query, "POINT({$lng} {$lat})", $name, $desc, $point_id);
	}

	/**
	 * Update single point
	 *
	 * @param string $key
	 * @param int $object_id
	 * @param float $lat
	 * @param float $lng
	 * @param string $name
	 * @param string $desc
	 *
	 * @return bool|int
	 */
	public function update_single_point($key, $object_id, $lat, $lng, $name = '', $desc = ''){
		if( $point = $this->get_point($key, $object_id) ){
			return $this->update_point($point->point_id, $lat, $lng, $name, $desc);
		}else{
			return $this->add_point($key, $object_id, $lat, $lng, $name, $desc);
		}
	}

	/**
	 * Delete point
	 *
	 * @param string $point_key
	 * @param int $object_id
	 *
	 * @return false|int
	 */
	public function delete_point($point_key, $object_id){
		return $this->delete(array(
			'point_key' => $point_key,
			'object_id' => $object_id,
		));
	}

	/**
	 * Count point
	 *
	 * @param string $key
	 * @param int $object_id
	 *
	 * @return int
	 */
	public function point_count($key, $object_id){
		$query = <<<SQL
			SELECT COUNT(point_id) FROM {$this->table}
			WHERE point_key = %s
			  AND object_id = %d
SQL;
		return (int) $this->get_var($query, $key, $object_id);
	}

	/**
	 * Get points
	 *
	 * @param string $key
	 * @param int $object_id
	 *
	 * @return array
	 */
	public function get_points($key, $object_id){
		return $this->retrieve_points($key, $object_id, false);
	}

	/**
	 * Get single point
	 *
	 * @param string $key
	 * @param int $object_id
	 *
	 * @return null|\stdClass
	 */
	public function get_point($key, $object_id){
		return $this->retrieve_points($key, $object_id, true);
	}

	/**
	 * Get points
	 *
	 * @param string $key
	 * @param int $object_id
	 * @param bool $single If false, return array
	 *
	 * @return null|\stdClass|array
	 */
	protected function retrieve_points($key, $object_id, $single = true){
		$query = <<<SQL
			SELECT
				point_id,
				point_key, point_name, point_desc,
				X(latlng) AS lng, Y(latlng) AS lat
			FROM {$this->table}
			WHERE point_key = %s
			  AND object_id = %d
SQL;
		if( $single ){
			return $this->get_row($query, $key, $object_id);
		}else{
			return $this->get_results($query, $key, $object_id);
		}
	}

	/**
	 * Retrieve non geo point
	 *
	 * @param string $post_type
	 * @param string $point_key
	 * @param int $limit
	 *
	 * @return array
	 */
	public function retrieve_non_geo_posts( $post_type, $point_key, $limit = 1000 ){
		$query = <<<SQL
			SELECT * FROM {$this->db->posts}
			WHERE post_type = %s
			  AND ID NOT IN (
			  	SELECT object_id FROM {$this->table}
			  	WHERE point_key = %s
			  )
			LIMIT %d
SQL;
		return $this->get_results($query, $post_type, $point_key, $limit);
	}

	/**
	 * Get latitude and longitude with GeoCoding
	 *
	 * @see https://developers.google.com/maps/documentation/geocoding/
	 * @param string $address
	 * @return \WP_Error|\stdClass
	 */
	public function geocode($address){
		$endpoint = 'https://maps.googleapis.com/maps/api/geocode/json?address='.rawurlencode($address);
		$response = wp_remote_get($endpoint);
		if( is_wp_error($response) ){
			return $response;
		}
		$json = json_decode($response['body']);
		if( !$json || 'OK' != $json->status ){
			return new \WP_Error(500, '座標を取得できませんでした。');
		}
		foreach( $json->results as $result ){
			if( $result->geometry ){
				return $result->geometry->location;
			}
		}
		return new \WP_Error(404, '座標を取得できませんでした。');
	}

}
