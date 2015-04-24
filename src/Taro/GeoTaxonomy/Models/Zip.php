<?php

namespace Taro\GeoTaxonomy\Models;


use Taro\Common\Pattern\Model;


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
