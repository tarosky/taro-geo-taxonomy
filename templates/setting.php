<?php
/**
 * Admin setting panel
 */

defined( 'ABSPATH' ) or die();

/** @var \Taro\GeoTaxonomy\Admin\Setting $this*/

?>

<div class="wrap wrap--tarogeo">
	<h2><?php $this->i18n->e( 'Taro Geo Taxonomy 設定ページ' ); ?></h2>

	<hr />

	<h3><span class="dashicons dashicons-admin-settings"></span> <?php $this->i18n->e( '一般設定' ); ?></h3>
	<form action="<?php echo admin_url( 'options-general.php?page=taro-geo-taxonomy' ); ?>" method="post">
		<?php wp_nonce_field( 'taro-geo-taxonomy' ); ?>
		<table class="form-table">
			<tr>
				<th><label for="taxonomy-name"><?php $this->i18n->e( 'タクソノミー名' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" id="taxonomy-name" name="taxonomy-name" value="<?php echo esc_attr( $this->taxonomy ); ?>" />
					<p class="description">
						<?php $this->i18n->e( 'タクソノミー名は半角英数(小文字）と-または_だけです。また、途中で変更するとこれまでのデータは失われます。' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th><label for="taxonomy-label"><?php $this->i18n->e( 'ラベル' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" id="taxonomy-label" name="taxonomy-label" value="<?php echo esc_attr( $this->label ); ?>" />
					<p class="description">
						<?php $this->i18n->e( 'アーカイブページのタイトルなどに使われます。' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th><label><?php $this->i18n->e( 'サポートする投稿タイプ' ); ?></label></th>
				<td>
					<?php foreach ( $this->get_post_types() as $post_type ) : ?>
						<label class="inline">
							<input type="checkbox" name="post_types[]" value="<?php echo esc_attr( $post_type->name ); ?>" <?php checked( $this->is_supported( $post_type->name ) ); ?> />
							<?php echo esc_html( $post_type->label ); ?>
						</label>
					<?php endforeach; ?>
				</td>
			</tr>
			<tr>
				<th><label for="google-api-key"><?php $this->i18n->e( 'Google Maps APIキー' ); ?></label></th>
				<td>
					<input type="text" name="google-api-key" id="google-api-key" value="<?php echo esc_attr( $this->option['api_key'] ); ?>" class="regular-text" />
					<p class="description">
						<?php esc_html_e( 'Googleマップ連携は停止されました。', 'taro-geo-tax' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th><label for="geolonia-key"><?php esc_html_e( 'Geolonia APIキー', 'taro-geo-tax' ); ?></label></th>
				<td>
					<input type="text" name="geolonia-key" id="geolonia-key" value="<?php echo esc_attr( $this->option['geolonia_key'] ); ?>" class="regular-text" />
					<p class="description">
						<?php
						// translators: %s is a URL.
						printf( __( '<a href="%s" target="_blank">Geolonia</a>でAPIキーを取得できます。', 'taro-geo-tax' ), 'https://geolonia.com' );
						?>
					</p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'ジオコーディング', 'taro-geo-tax' ); ?></th>
				<td>
					<p>
						<label>
							<?php esc_html_e( 'AWSアクセスキー', 'taro-geo-tax' ); ?>
							<br />
							<input type="text" name="aws-access-key" class="regular-text"
								value="<?php echo esc_attr( $this->option['aws_access_key'] ); ?>" />
						</label>
					</p>
					<p>
						<label>
							<?php esc_html_e( 'AWSアクセスシークレット', 'taro-geo-tax' ); ?>
							<br />
							<input type="text" name="aws-access-secret" class="regular-text"
								value="<?php echo esc_attr( $this->option['aws_access_secret'] ); ?>" />
						</label>
					</p>
					<p>
						<label>
							<?php esc_html_e( 'インデックス名', 'taro-geo-tax' ); ?>
							<br />
							<input type="text" name="aws-index-name" class="regular-text"
								value="<?php echo esc_attr( $this->option['aws_index_name'] ); ?>" />
						</label>
					</p>
					<p class="description">
						<?php esc_html_e( '住所を緯度経度に変換するためにAmazon Location Serviceを利用します。', 'taro-geo-tax' ); ?>
						&raquo;<a href="https://aws.amazon.com/jp/location/" target="_blank" rel="noopener noreferrer">Amazon Location Service</a>
					</p>
					<p>
						<label>
							<?php esc_html_e( '国', 'taro-geo-tax' ); ?>
							<br />
							<input type="text" name="country" class="regular-text"
								value="<?php echo esc_attr( $this->option['country'] ); ?>" />
						</label>
					</p>
					<p class="description">
						<?php esc_html_e( '緯度経度変換の際に国を絞る場合はISO 3166に従った3文字の国名コードをカンマ区切り形式で入力してください。', 'taro-geo-tax' ); ?>
						&raquo;<a href="https://www.iso.org/obp/ui/#search" target="_blank" rel="noopener noreferrer">ISO 3166</a>
					</p>
				</td>
			</tr>
		</table>
		<?php submit_button( __( '更新', 'taro-geo-tax' ) ); ?>
	</form>

	<hr />

	<h3><span class="dashicons dashicons-download"></span> <?php $this->i18n->e( 'インポート' ); ?></h3>

	<div class="geo-importer">

		<form id="taro-geo-import-form" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post">
			<input type="hidden" name="action" value="taro-geo-import" />
			<?php wp_nonce_field( 'taro-geo-import', '_wpnonce', false ); ?>
			<input type="hidden" name="step" value="1" />
			<input type="hidden" name="rows" value="0" />
			<p class="description">
				<?php echo $this->option['source']['description']; ?>
				<?php $this->i18n->p( '現在、%s件の地域情報が保存されています。', number_format( \Taro\GeoTaxonomy\Models\Zip::get_instance()->total() ) ); ?>
			</p>
			<?php submit_button( $this->i18n->s( 'インポート' ) ); ?>
			<pre></pre>
		</form>
	</div>

	<hr />

	<h3><span class="dashicons dashicons-update"></span> <?php $this->i18n->e( '同期' ); ?></h3>

	<div class="geo-importer">

		<form id="taro-geo-sync-form" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post">
			<input type="hidden" name="action" value="taro-geo-sync" />
			<?php wp_nonce_field( 'taro-geo-sync', '_wpnonce', false ); ?>
			<input type="hidden" name="step" value="1" />
			<input type="hidden" name="rows" value="0" />
			<p class="description">
				<?php $this->i18n->p( '現在、%s件の市区町村が保存されています。これらの情報をタクソノミーとして保存します。保存されるのは市区町村までです。', number_format( \Taro\GeoTaxonomy\Models\Zip::get_instance()->city_total() ) ); ?>
			</p>
			<?php submit_button( $this->i18n->s( 'インポート' ) ); ?>
			<pre></pre>
		</form>

	</div>


</div><!--- //.wrap -->
