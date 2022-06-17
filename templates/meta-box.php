<?php
/**
 * Meta box for edit screen
 */

defined( 'ABSPATH' ) or die();

/** @var Taro\GeoTaxonomy\Admin\MetaBox $this*/
/** @var WP_Post $post */

$address = new \Taro\GeoTaxonomy\Helper\Address( $post );


/**
 * taro_geo_before_meta_box
 *
 * Executed before meta box.
 *
 * @param WP_Post $post
 * @param Taro\GeoTaxonomy\Helper\Address $address
 */
do_action( 'taro_geo_before_meta_box', $post, $address );
?>
<table class="form-table">

	<tr>
		<th><label for="zip"><?php esc_html_e( '郵便番号', 'taro-geo-tax' ); ?></label></th>
		<td>
			<input type="text" placeholder="000-0000" name="zip" id="zip" value="<?php echo esc_attr( $address->zip ); ?>" />
			<a class="taro-zip-search button" href="#" data-target="#zip"><?php esc_html_e( '郵便番号検索', 'taro-geo-tax' ); ?></a>
		</td>
	</tr>
	<tr>
		<th><label for="prefecture"><?php esc_html_e( '都道府県', 'taro-geo-tax' ); ?></label></th>
		<td>
			<select id="prefecture" name="prefecture">
				<option value="0"><?php esc_html_e( '選択してください', 'taro-geo-tax' ); ?></option>
				<?php foreach ( $address->get_prefectures() as $pref ) : ?>
					<option value="<?php echo esc_attr( $pref->term_id ); ?>"<?php selected( has_term( $pref->term_id, $pref->taxonomy, $post ) ); ?>>
						<?php echo esc_html( $pref->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><label for="city"><?php esc_html_e( '市区町村', 'taro-geo-tax' ); ?></label></th>
		<td>
			<input type="text" name="city" id="city" value="" data-prefecture="<?php echo $address->prefecture->term_id; ?>"
																						<?php
																						if ( $address->city->term_id ) {
																							printf( ' data-id="%s" data-name="%s"', $address->city->term_id, esc_attr( $address->city->name ) );
																						}
																						?>
			/>
		</td>
	</tr>
	<tr>
		<th><?php esc_html_e( '住所', 'taro-geo-tax' ); ?></th>
		<td>
			<label><input class="regular-text" id="street" name="street" value="<?php echo esc_attr( $address->street ); ?>" > <span class="description"><?php esc_html_e( '住所', 'taro-geo-tax' ); ?></span></label>
			<label><input class="regular-text" id="building" name="building" value="<?php echo esc_attr( $address->building ); ?>" > <span class="description"><?php esc_html_e( '建物', 'taro-geo-tax' ); ?></span></label>
		</td>
	</tr>
	<tr>
		<th><?php esc_html_e( '座標', 'taro-geo-tax' ); ?></th>
		<td>
			<label><input class="regular-text" id="lat" name="lat" value="<?php echo esc_attr( $address->lat ); ?>" readonly > <span class="description"><?php esc_html_e( '緯度', 'taro-geo-tax' ); ?></span></label>
			<label><input class="regular-text" id="lng" name="lng" value="<?php echo esc_attr( $address->lng ); ?>" readonly > <span class="description"><?php esc_html_e( '経度', 'taro-geo-tax' ); ?></span></label>
			<p class="description">
				<?php esc_html_e( '住所から検索して入力する', 'taro-geo-tax' ); ?>
				<a id="taro-geo-searcher" class="button" href="#"><?php esc_html_e( '検索', 'taro-geo-tax' ); ?></a>
				<a id="taro-geo-clearer" class="button" href="#"><?php esc_html_e( 'クリア', 'taro-geo-tax' ); ?></a>
			</p>
			<input type="hidden" id="address-src" name="address-src" value="<?php echo esc_attr( $address->src ); ?>" />
		</td>
	</tr>
</table>
<div id="geolonia-map" style="width: 100%; height: 400px"><strong><?php echo esc_html( get_the_title( $post ) ); ?></strong></div>
<?php

/**
 * taro_geo_after_meta_box
 *
 * Executed after meta box.
 *
 * @param WP_Post $post
 * @param Taro\GeoTaxonomy\Helper\Address $address
 */
do_action( 'taro_geo_after_meta_box', $post, $address );
