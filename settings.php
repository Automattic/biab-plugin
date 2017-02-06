<?php

function save_to_config( $settings ) {
	$file = get_home_path().'photo.config';
	$flip = [];

	if ( $settings['vflip'] ) {
		$flip[] = '-vf';
	}

	if ( $settings['hflip'] ) {
		$flip[] = '-hf';
	}

	$config = array(
		'QUALITY='.$settings['quality'],
		'FLIP="'.implode( ' ', $flip ).'"',
		'',
	);

	file_put_contents( $file, implode( "\n", $config ) );
}

function biab_settings() {
	$settings = get_option( 'biab_settings' );

	if ( isset( $_POST['quality'] ) && check_admin_referer( 'biab_settings' ) ) {
		$quality = intval( $_POST['quality'], 10 );
		$vflip = isset( $_POST['vertical'] ) ? true : false;
		$hflip = isset( $_POST['horizontal'] ) ? true : false;

		$settings = array(
			'quality' => $quality,
			'vflip' => $vflip,
			'hflip' => $hflip,
		);

		save_to_config( $settings );

		update_option( 'biab_settings', $settings );
?>
	<div class="notice notice-success is-dismissible">
        	<p><?php _e( 'Done!', 'blog-in-a-box' ); ?></p>
    	</div>

<?php
	}

?>
	<div style="padding:32px">
    <div style="display:flex">
        <div style="margin-right:32px">
            <img src="<?php echo plugins_url( 'wapi-512.png', __FILE__ ); ?>" width="128">
        </div>
		<div>
	        <h1>Blog In A Box Settings</h1>

			<form method="post">
				<p><label><input type="checkbox" name="vertical" <?php checked( $settings['vflip'] ); ?>/> Vertical flip</label></p>
				<p><label><input type="checkbox" name="horizontal" <?php checked( $settings['hflip'] ); ?>/> Horizontal flip</label></p>
				Quality: <input type="number" name="quality" min="0" max="100" step="1" value="<?php echo esc_attr( $settings['quality'] ); ?>"/>
				<input type="submit" value="Save"/>

				<?php wp_nonce_field( 'biab_settings' ); ?>
			</form>

			<div id="result"></div>
		</div>
    </div>
<?php
}
