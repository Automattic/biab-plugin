<?php

// https://www.raspberrypi.org/documentation/raspbian/applications/camera.md

include dirname( __FILE__ ).'/cron.php';
include dirname( __FILE__ ).'/control.php';
include dirname( __FILE__ ).'/settings.php';

class BiabCamera {
	private static $instance = null;

	static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new BiabCamera();
		}

		return self::$instance;
	}

	public function __construct() {
		$enabled = biab_is_module_enabled( 'camera' );

		if ( $enabled ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_post_biab_take_photo', array( $this, 'take_photo' ) );
			add_action( 'admin_post_biab_camera_schedule', array( $this, 'set_schedule' ) );
			add_action( 'admin_post_biab_camera_settings', array( $this, 'save_settings' ) );
			add_action( 'blog-in-a-box_page_biab-plugin-camera', array( $this, 'wp_head' ) );
		}
	}

	public function admin_menu() {
		add_submenu_page( 'biab-plugin', __( 'Camera', 'bloginbox' ), __( 'Camera', 'bloginbox' ), 'manage_options', 'biab-plugin-camera', array( $this, 'show_page' ) );
	}

	public function wp_head() {
		wp_enqueue_style( 'bloginbox', plugin_dir_url( BIAB_FILE ).'plugin.css' );
		wp_enqueue_style( 'bloginbox-camera', plugin_dir_url( __FILE__ ).'camera.css' );
		wp_enqueue_script( 'biab-camera', plugin_dir_url( __FILE__ ).'camera.js', 'jquery' );
	}

	public function take_photo() {
		$control = new CameraControl();
		$result = $control->take_photo();

		if ( $result !== false ) {
			echo json_encode( array(
				'post_id' => $result,
				'image' => get_the_post_thumbnail( $result ),
				'url' => get_post_permalink( $result ),
			) );
			return;
		}

		echo json_encode( array( 'error' => true ) );
	}

	public function set_schedule() {
		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'biab_camera-schedule' ) ) {
			$cron = new CameraCron();
			$cron->set_interval( intval( $_POST['interval'], 10 ) );
			$cron->set_period( $_POST['period'] );

			$control = new CameraControl();
			if ( $control->set_schedule( $cron->get_interval(), $cron->get_period() ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=biab-plugin-camera&msg=saved' ) );
			} else {
				wp_safe_redirect( admin_url( 'admin.php?page=biab-plugin-camera&msg=savefail' ) );
			}
		}
	}

	public function save_settings() {
		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'biab_camera-settings' ) ) {
			$settings = new CameraSettings();
			$settings->save( $_POST );

			$redirect = add_query_arg( array( 'page' => 'biab-plugin-camera', 'sub' => 'settings' ), admin_url( 'admin.php' ) );
			$control = new CameraControl();

			if ( $control->save_settings( $settings->get() ) ) {
				$redirect = add_query_arg( 'msg', 'saved', $redirect );
			} else {
				$redirect = add_query_arg( 'msg', 'savefail', $redirect );
			}

			$control->take_snapshot();
			wp_safe_redirect( $redirect );
		}
	}

	public function show_page() {
		$menu = 'control';
		if ( isset( $_GET['sub'] ) && $_GET['sub'] === 'settings' ) {
			$menu = 'settings';
		}
?>
	<div class="wrap">
		<?php if ( isset( $_GET['msg'] ) && $_GET['msg'] === 'saved' ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php _e( 'Settings have been saved', 'bloginbox' ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( isset( $_GET['msg'] ) && $_GET['msg'] === 'savefail' ) : ?>
			<div class="notice notice-error">
				<p><?php _e( 'Failed to update settings', 'bloginbox' ); ?></p>
			</div>
		<?php endif; ?>

		<h1><?php _e( 'Blog In A Box &ndash; Camera', 'bloginbox' ); ?></h1>

		<div class="biab-wapuu">
			<img src="<?php echo plugins_url( 'wapi-512.png', BIAB_FILE ); ?>" width="128" />
		</div>

		<?php $this->show_menu( $menu ); ?>

		<?php if ( $menu === 'settings' ) : ?>
			<?php $this->show_page_settings(); ?>
		<?php elseif ( $menu === 'control' ) : ?>
			<?php $this->show_page_control(); ?>
		<?php endif; ?>
	</div>
<?php
	}

	private function show_menu( $menu ) {
?>
	<ul class="subsubsub">
		<li>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=biab-plugin-camera' ) ); ?>"<?php echo $menu === 'control' ? ' class="current"' : ''; ?>>
				<?php _e( 'Control', 'bloginbox' ); ?>
			</a>
		</li> |
		<li>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=biab-plugin-camera&sub=settings' ) ); ?>"<?php echo $menu === 'settings' ? ' class="current"' : ''; ?>>
				<?php _e( 'Settings', 'bloginbox' ); ?>
			</a>
		</li>
	</ul>
<?php
	}

	private function show_page_control() {
		$control = new BiabControl();
		$cron = new CameraCron();

		add_thickbox();
?>
	<div class="card">
		<h2 class="subsubsubheader"><?php _e( 'Camera Control', 'bloginbox' ); ?></h2>
		<p><?php _e( 'With an attached <a target="_blank" href="https://www.raspberrypi.org/products/camera-module/">camera module</a> you can take a photo and have it automatically added to a new blog post.', 'bloginbox' ); ?></p>

		<h3><?php _e( 'Manual Photo', 'bloginbox' ); ?></h3>
		<p><?php _e( 'Take a photo right now by clicking this button.', 'bloginbox' ); ?></p>
		<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
			<input type="hidden" name="action" value="biab_take_photo" />
			<?php wp_nonce_field( 'biab_camera-takephoto' ); ?>

			<p><a href="#TB_inline?width=600&amp;height=500&amp;inlineId=camera-lens" title="<?php echo esc_attr( __( 'Taking a photo' ) ); ?>" id="take-photo" class="button">
				<?php _e( 'Take Photo', 'bloginbox' ); ?>
			</a></p>

			<div id="camera-loading" style="display:none;"><img src="/wp-includes/images/wpspin.gif"/></div>
			<div id="camera-failed" style="display:none;"><?php _e( 'Failed to take a picture', 'bloginbox' ); ?></div>
			<div id="camera-lens" style="display:none;"><img src="/wp-includes/images/wpspin.gif"/></div>
		</form>
	</div>

	<div class="card">
		<h3><?php _e( 'Scheduled Photo', 'bloginbox' ); ?></h3>
		<p><?php _e( 'Take a photo on a schedule by setting a period between each photo.', 'bloginbox' ); ?>:</p>

		<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
			<p>
				<input type="number" name="interval" min="0" style="width: 50px" value="<?php echo esc_attr( $cron->get_interval() ); ?>"/>
				<select name="period">
					<?php foreach ( $cron->get_periods() as $key => $name ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $cron->get_period(), $key ) ?>><?php echo esc_html( $name ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>

			<?php submit_button( false, 'button-secondary' ); ?>

			<input type="hidden" name="action" value="biab_camera_schedule" />
			<?php wp_nonce_field( 'biab_camera-schedule' ); ?>
		</form>
	</div>

	<div class="card">
		<h3><?php _e( 'Externally Triggered Photo', 'bloginbox' ); ?></h3>
		<p><?php printf( __( 'Trigger a photo externally by hooking your trigger to the command <code>%s</code>.', 'bloginbox' ), BiabControl::COMMAND ); ?></p>
		<p><?php _e( 'For example:', 'bloginbox' ); ?></p>
		<p><code><?php echo esc_html( $control->get_path().'/'.BiabControl::COMMAND ) ?> camera-take-photo [<?php _e( 'title', 'bloginbox' ); ?>]</code></p>
	</div>
<?php
	}

	private function show_page_settings() {
		$settings = new CameraSettings();

		$snapshot = wp_upload_dir();
		if ( ! file_exists( $snapshot['basedir'].'/snapshot.jpg' ) ) {
			$control = new CameraControl();
			$control->take_snapshot();
		}
?>

	<h2 class="subsubsubheader"><?php _e( 'Camera Settings', 'bloginbox' ); ?></h2>

	<p><?php _e( 'These settings affect the image taken by the camera. Changing a value will update the image preview.', 'bloginbox' ); ?></p>

	<div class="camera-snapshot">
		<img src="/wp-content/uploads/snapshot.jpg" width="400" height="300" alt="" />
	</div>

	<form method="POST" action="<?php echo admin_url( 'admin-post.php' ); ?>">
		<table class="form-table">
			<tr>
				<th><?php _e( 'Vertical flip', 'bloginbox' ); ?></th>
				<td><input type="checkbox" name="vertical" <?php checked( $settings->is_enabled( 'vflip' ) ); ?>/></td>
			</tr>
			<tr>
				<th><?php _e( 'Horizontal flip', 'bloginbox' ); ?></th>
				<td><input type="checkbox" name="horizontal" <?php checked( $settings->is_enabled( 'hflip' ) ); ?>/></td>
			</tr>
			<tr>
				<th><?php _e( 'Quality', 'bloginbox' ); ?></th>
				<td><input type="number" name="quality" min="0" max="100" step="1" value="<?php echo esc_attr( $settings->get_value( 'quality' ) ); ?>"/> <?php _e( '0 to 100' ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Brightness', 'bloginbox' ); ?></th>
				<td><input type="number" name="brightness" min="0" max="100" step="1" value="<?php echo esc_attr( $settings->get_value( 'brightness' ) ); ?>"/> <?php _e( '0 to 100' ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Saturation', 'bloginbox' ); ?></th>
				<td><input type="number" name="saturation" min="-100" max="100" step="1" value="<?php echo esc_attr( $settings->get_value( 'saturation' ) ); ?>"/> <?php _e( '-100 to 100' ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Sharpness', 'bloginbox' ); ?></th>
				<td><input type="number" name="sharpness" min="-100" max="100" step="1" value="<?php echo esc_attr( $settings->get_value( 'sharpness' ) ); ?>"/> <?php _e( '-100 to 100' ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Contrast', 'bloginbox' ); ?></th>
				<td><input type="number" name="contrast" min="-100" max="100" step="1" value="<?php echo esc_attr( $settings->get_value( 'contrast' ) ); ?>"/> <?php _e( '-100 to 100' ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'ISO', 'bloginbox' ); ?></th>
				<td>
					<select name="iso">
						<?php foreach ( $settings->get_iso_values() as $iso ) : ?>
							<option <?php selected( $settings->get_value( 'iso' ), $iso ); ?> value="<?php echo esc_attr( $iso ); ?>">
								<?php echo esc_html( $iso ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><?php _e( 'White Balance', 'bloginbox' ); ?></th>
				<td>
					<select name="awb">
						<?php foreach ( $settings->get_awb_values() as $awb_key => $awb_name ) : ?>
							<option <?php selected( $settings->get_value( 'awb' ), $awb_key ); ?> value="<?php echo esc_attr( $awb_key ); ?>">
								<?php echo esc_html( $awb_name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><?php _e( 'Effect', 'bloginbox' ); ?></th>
				<td>
					<select name="effect">
						<?php foreach ( $settings->get_effect_values() as $name => $title ) : ?>
							<option <?php selected( $settings->get_value( 'ifx' ), $name ); ?> value="<?php echo esc_attr( $name ); ?>">
								<?php echo esc_html( $title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>

		<input type="hidden" name="action" value="biab_camera_settings" />
		<?php wp_nonce_field( 'biab_camera-settings' ); ?>
		<?php submit_button(); ?>
	</form>
<?php
	}
}

add_action( 'init', array( 'BiabCamera', 'init' ), 11 );
