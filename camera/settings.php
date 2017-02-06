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
		'OTHER="--imxfx '.$settings['ifx'].'"',
		'',
	);

	file_put_contents( $file, implode( "\n", $config ) );
}

function biab_get_camera_effects() {
	return array(
		'none' => 'None',
		'negative' => 'Negative',
		'solarise' => 'Solarise',
		'sketch' => 'Sketch',
		'denoise' => 'Denoise',
		'emboss' => 'Emboss',
		'oilpaint' => 'Oil Painting',
		'hatch' => 'Hatch',
		'gpen' => 'GPEN',
		'pastel' => 'Pastel',
		'watercolour' => 'Watercolour',
		'film' => 'Film',
		'blur' => 'Blur',
		'saturation' => 'Saturation',
		'colourswap' => 'Colour Swap',
		'washedout' => 'Washed Out',
		'posterise' => 'Posterise',
		'colourpoint' => 'Colour Point',
		'colourbalance' => 'Colour Balance',
		'cartoon' => 'Cartoon',
	);
}

function biab_settings() {
	$settings = get_option( 'biab_settings' );
	$effects = biab_get_camera_effects();

	if ( isset( $_POST['quality'] ) && check_admin_referer( 'biab_settings' ) ) {
		$quality = intval( $_POST['quality'], 10 );
		$vflip = isset( $_POST['vertical'] ) ? true : false;
		$hflip = isset( $_POST['horizontal'] ) ? true : false;
		$sharpness = intval( $_POST['sharpness'], 10 );
		$contrast = intval( $_POST['contrast'], 10 );
		$brightness = intval( $_POST['brightness'], 10 );
		$saturation = intval( $_POST['saturation'], 10 );

		$sharpness = min( 100, max( -100, $sharpness ) );
		$contrast = min( 100, max( -100, $contrast ) );
		$brightness = min( 100, max( 0, $brightness ) );
		$saturation = min( 100, max( -100, $saturation ) );

		$ifx = '';
		if ( isset( $effects[$_POST['effect'] ] ) ) {
			$ifx = $_POST['effect'];
		}

		$settings = array(
			'quality' => $quality,
			'vflip' => $vflip,
			'hflip' => $hflip,
			'ifx' => $ifx,
			'sharpness' => $sharpness,
			'contrast' => $contrast,
			'brightness' => $brightness,
			'saturation' => $saturation,
		);

		save_to_config( $settings );

		update_option( 'biab_settings', $settings );

		exec( '/opt/wp/snapshot.sh' );
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
				<p>Quality: <input type="number" name="quality" min="0" max="100" step="1" value="<?php echo esc_attr( $settings['quality'] ); ?>"/> 0 to 100</p>
				<p>Brightness: <input type="number" name="brightness" min="0" max="100" step="1" value="<?php echo esc_attr( $settings['brightness'] ); ?>"/> 0 to 100</p>
				<p>Saturation: <input type="number" name="saturation" min="-100" max="100" step="1" value="<?php echo esc_attr( $settings['saturation'] ); ?>"/> -100 to 100</p>
				<p>Sharpness: <input type="number" name="sharpness" min="-100" max="100" step="1" value="<?php echo esc_attr( $settings['sharpness'] ); ?>"/> -100 to 100</p>
				<p>Contrast: <input type="number" name="contrast" min="-100" max="100" step="1" value="<?php echo esc_attr( $settings['contrast'] ); ?>"/> -100 to 100</p>

				<select name="effect">
					<?php foreach ( $effects as $name => $title ) : ?>
						<option <?php selected( $settings['ifx'], $name ); ?> value="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $title ); ?></option>
					<?php endforeach; ?>
				</select>

				<input type="submit" value="Save"/>

				<?php wp_nonce_field( 'biab_settings' ); ?>
			</form>

			<img src="/wp-content/uploads/snapshot.jpg" width="640" height="480"/>
		</div>
    </div>
<?php
}
