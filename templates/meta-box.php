<?php
/**
 * Meta box for edit screen
 */

defined('ABSPATH') or die();

/** @var Taro\GeoTaxonomy\Admin\MetaBox $this*/
/** @var WP_Post $post */

$address = new \Taro\GeoTaxonomy\Helper\Address($post);
?>
<table class="form-table">

	<tr>
		<th><label for="zip">郵便番号</label></th>
		<td>
			<input type="text" placeholder="000-0000" name="zip" id="zip" value="<?php echo esc_attr($address->zip) ?>" />
			<a class="taro-zip-search button" href="#" data-target="#zip"><?php $this->i18n->e('郵便番号検索') ?></a>
		</td>
	</tr>
	<tr>
		<th><label for="prefecture">都道府県</label></th>
		<td>
			<select id="prefecture" name="prefecture">
				<option value="0"><?php $this->i18n->e('選択してください') ?></option>
				<?php foreach( $address->get_prefectures() as $pref ): ?>
					<option value="<?php echo esc_attr($pref->term_id) ?>"<?php selected(has_term($pref->term_id, $pref->taxonomy, $post)) ?>><?php echo esc_html($pref->name) ?></option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><label for="city">市区町村</label></th>
		<td>
			<input type="text" name="city" id="city" value="" data-prefecture="<?php echo $address->prefecture->term_id ?>" <?php if( $address->city->term_id ) printf(' data-id="%s" data-name="%s"', $address->city->term_id, esc_attr($address->city->name)) ?> />
		</td>
	</tr>
	<tr>
		<th>住所</th>
		<td>
			<label><input class="regular-text" id="street" name="street" value="<?php echo esc_attr($address->street) ?>" > <span class="description">住所</span></label>
			<label><input class="regular-text" id="building" name="building" value="<?php echo esc_attr($address->building) ?>" > <span class="description">建物</span></label>
		</td>
	</tr>
	<tr>
		<th>座標</th>
		<td>
			<label><input class="regular-text" id="lat" name="lat" value="<?php echo esc_attr($address->lat) ?>" readonly > <span class="description">緯度</span></label>
			<label><input class="regular-text" id="lng" name="lng" value="<?php echo esc_attr($address->lng) ?>" readonly > <span class="description">経度</span></label>
			<p class="description">
				住所から検索して入力する <a id="taro-geo-searcher" class="button" href="#">検索</a> <a id="taro-geo-clearer" class="button" href="#">クリア</a>
			</p>
		</td>
	</tr>
</table>
<div id="taro-gmap-container"></div>
