<?php

namespace Taro\GeoTaxonomy\Utility;


use Taro\GeoTaxonomy\Bootstrap;


class Prefecture
{



	/**
	 * Prohibit new
	 */
	private function __construct(){}

	/**
	 * All Prefectures
	 *
	 * @var array
	 */
	public static $prefs = array(
		'北海道' => array(
			'北海道',
		),
		'東北' => array(
			'青森県',
			'岩手県',
			'宮城県',
			'秋田県',
			'山形県',
			'福島県',
		),
		'関東' => array(
			'茨城県',
			'栃木県',
			'群馬県',
			'埼玉県',
			'千葉県',
			'東京都',
			'神奈川県',
		),
		'甲信越' => array(
			'新潟県',
			'山梨県',
			'長野県',
		),
		'北陸' => array(
			'富山県',
			'石川県',
			'福井県',
		),
		'東海' => array(
			'岐阜県',
			'静岡県',
			'愛知県',
			'三重県',
		),
		'近畿' => array(
			'滋賀県',
			'京都府',
			'大阪府',
			'兵庫県',
			'奈良県',
			'和歌山県',
		),
		'中国' => array(
			'鳥取県',
			'島根県',
			'岡山県',
			'広島県',
			'山口県',
		),
		'四国' => array(
			'徳島県',
			'香川県',
			'愛媛県',
			'高知県',
		),
		'九州・沖縄' => array(
			'福岡県',
			'佐賀県',
			'長崎県',
			'熊本県',
			'大分県',
			'宮崎県',
			'鹿児島県',
			'沖縄県',
		),
	);

	/**
	 * オプションタグを書き出す
	 *
	 * @param string $selected
	 * @param bool $optgoup
	 * @param bool $use_cache
	 */
	public static function options($selected = '', $optgoup = true, $use_cache = true){
		/** @var \wpdb $wpdb */
		global $wpdb;
		$pref_ids = array();
		$in = array();
		foreach( self::$prefs as $region => $prefs ){
			foreach( $prefs as $pref ){
				$in[] = $pref;
				$pref_ids[$pref] = 0;
			}
		}
		$in = implode(', ', array_map(function($pref) use ($wpdb){
			return $wpdb->prepare('%s', $pref);
		}, $in));
		$taxonomy = BootStrap::get_instance()->taxonomy;
		$query = <<<SQL
			SELECT t.term_id, t.name FROM {$wpdb->terms} AS t
			INNER JOIN {$wpdb->term_taxonomy} AS tt
			ON t.term_id = tt.term_id
			WHERE tt.taxonomy = %s
			  AND t.name IN ({$in})
SQL;
		foreach( $wpdb->get_results($wpdb->prepare($query, $taxonomy)) as $row ){
			$pref_ids[$row->name] = intval($row->term_id);
		}

		foreach( self::$prefs as $region => $prefs ){
			$options = array_map( function($p) use ($selected, $pref_ids){
				return sprintf('<option value="%d"%s>%s</option>', esc_attr($pref_ids[$p]), selected($pref_ids[$p] == $selected, true, false), esc_html($p));
			}, $prefs);
			if( $optgoup ){
				printf('<optgroup label="%s">%s</optgroup>', esc_attr($region), implode("\n", $options));
			}else{
				echo implode("\n", $options);
			}
		}
	}

	/**
	 * 都道府県名のリストを取得する
	 *
	 * @param bool $tofuken
	 * @return array
	 */
	public static function get_pref($tofuken = true){
		$prefs = array();
		foreach( self::$prefs as $region => $prefs ){
			$prefs = array_merge($prefs, array_map(function($pref) use ($tofuken){
				return !$tofuken ? Prefecture::remove_tofuken($pref) : $pref;
			}, $prefs));
		}
		return $prefs;
	}

	/**
	 * 都府県を外す
	 *
	 * @param string $pref
	 *
	 * @return mixed
	 */
	public static function remove_tofuken($pref){
		return preg_replace('/(都|府|県)$/u', '', $pref);
	}
}
