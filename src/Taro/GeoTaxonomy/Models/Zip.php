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

	/**
	 * Create zip table
	 *
	 * @return string
	 */
	protected function create_sql() {
		return <<<SQL
			CREATE TABLE `{$this->table}` (
				area_id BIGINT NOT NULL AUTO_INCREMENT,
				zip VARCHAR(12) NOT NULL,
				prefecture VARCHAR(96) NOT NULL,
				city VARCHAR(96) NOT NULL,
				town VARCHAR(256) NOT NULL,
				prefecture_yomi VARCHAR(256) NOT NULL,
				city_yomi_yomi VARCHAR(256) NOT NULL,
				town_yomi_yomi VARCHAR(256) NOT NULL,
				PRIMARY KEY (area_id),
				INDEX zip_search (zip(3)),
				INDEX city_search (prefecture, city)
			) ENGINE = InnoDB DEFAULT CHARSET utf8
SQL;
	}


}
