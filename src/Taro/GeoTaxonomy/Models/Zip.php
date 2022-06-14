<?php

namespace Taro\GeoTaxonomy\Models;


use Taro\GeoTaxonomy\Pattern\Model;


/**
 * Create zip search table
 *
 * @package Taro\GeoTaxonomy\Model
 */
class Zip extends Model
{

	protected $name = 'areas';

	protected $version = '1.0';

	protected $where_clause = array(
		'area_id' => '%d',
	);

	/**
	 * Create zip table
	 *
	 * @return string
	 */
	protected function create_sql() {
		$code = $this->has_utf8mb4() ? 'utf8mb4' : 'utf8';
		return <<<SQL
			CREATE TABLE `{$this->table}` (
				area_id BIGINT NOT NULL AUTO_INCREMENT,
				zip VARCHAR(12) NOT NULL,
				prefecture VARCHAR(96) NOT NULL,
				city VARCHAR(96) NOT NULL,
				town VARCHAR(256) NOT NULL,
				prefecture_yomi VARCHAR(256) NOT NULL,
				city_yomi VARCHAR(256) NOT NULL,
				town_yomi VARCHAR(256) NOT NULL,
				PRIMARY KEY (area_id),
				INDEX zip_search (zip(3)),
				INDEX city_search (prefecture, city)
			) ENGINE = InnoDB DEFAULT CHARSET {$code}
SQL;
	}

	/**
	 * Add data
	 *
	 * @param string $zip
	 * @param string $prefecture
	 * @param string $city
	 * @param string $town
	 * @param string $prefecture_yomi
	 * @param string $city_yomi
	 * @param string $town_yomi
	 *
	 * @return bool
	 */
	public function add($zip, $prefecture, $city, $town, $prefecture_yomi = '', $city_yomi = '', $town_yomi = ''){
		$zip = $this->normalize_zip($zip);
		$data = compact('zip', 'prefecture', 'city', 'town', 'prefecture_yomi' , 'city_yomi', 'town_yomi');
		if( $address = $this->get_by_zip($zip) ){
			// Record exists, so update
			return (bool) $this->update($data, array(
				'area_id' => $address->area_id
			));
		}else{
			// Doesn't exist, insert
			return (bool) $this->insert($data);
		}

	}

	/**
	 * Get address by ZIP
	 *
	 * @param string $zip
	 *
	 * @return null|\stdClass
	 */
	public function get_by_zip($zip){
		$zip = $this->normalize_zip($zip);
		if( 7 === strlen($zip) ){
			return $this->get_row("SELECT * FROM {$this->table} WHERE zip = %s", $zip);
		}else{
			return $this->get_row("SELECT * FROM {$this->table} WHERE zip LIKE %s", '%'.$zip);
		}
	}

	/**
	 * Show city total
	 *
	 * @return int
	 */
	public function city_total(){
		return (int) $this->get_var("SELECT COUNT(distinct prefecture, city) FROM {$this->table}");
	}

	/**
	 * Get total row count
	 *
	 * @return int
	 */
	public function total(){
		return (int) $this->get_var("SELECT COUNT(area_id) FROM {$this->table}");
	}

	/**
	 * Get city list
	 *
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return array
	 */
	public function get_cities($offset = 0, $limit = 100){
		$query = <<<SQL
			SELECT DISTINCT
				prefecture, city, prefecture_yomi, city_yomi
				FROM {$this->table}
			LIMIT %d, %d
SQL;
		return $this->get_results($query, $offset, $limit);
	}

	/**
	 * Search city with term
	 *
	 * @param string $s
	 * @param int $parent_id
	 *
	 * @return array
	 */
	public function search_city($s, $parent_id = 0){
		$wheres = array(
			$this->db->prepare("( t.name LIKE %s OR tt.description LIKE %s )", "%{$s}%", "%{$s}%")
		);
		if( $parent_id ){
			array_unshift($wheres, $this->db->prepare("( tt.parent = %d )", $parent_id));
		}else{
			array_unshift($wheres, "( tt.parent IS NOT 0 )");
		}
		array_unshift($wheres, $this->db->prepare("( tt.taxonomy = %s )", $this->taxonomy));
		$where_clause = implode(' AND ', $wheres);
		$query = <<<SQL
			SELECT t.term_id, t.name, tt.description
			FROM {$this->db->terms} AS t
			INNER JOIN {$this->db->term_taxonomy} AS tt
			ON t.term_id = tt.term_id
			WHERE {$where_clause}
			ORDER BY t.term_id ASC
SQL;
		$result = $this->get_results($query);
		return $result;
	}

	/**
	 * Get address from zip
	 *
	 * @param string $zip
	 * @param bool   $multiple
	 *
	 * @return null|array|\stdClass
	 */
	public function search_from_zip($zip, $multiple = false){
		$query = <<<SQL
			SELECT * FROM {$this->table}
			WHERE zip LIKE %s
SQL;
		if( $multiple ){
			return $this->get_results($query, "{$zip}%");
		}else{
			return $this->get_row($query, "{$zip}%");
		}
	}

	/**
	 * Normalize zip
	 *
	 * @param string $zip
	 *
	 * @return string
	 */
	public function normalize_zip($zip){
		return preg_replace('/[^0-9]/', '', $zip);
	}


	/**
	 * Format zip
	 *
	 * @param string $zip
	 *
	 * @return mixed
	 */
	public function format_zip($zip){
		$zip = $this->normalize_zip($zip);
		return preg_replace('/[0-9]{3}[0-9]{4}/', '$1-$2', $zip);
	}


}
