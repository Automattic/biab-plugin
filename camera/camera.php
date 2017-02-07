<?php

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

			$this->cron = new CameraCron();
		}
	}

	public function admin_menu() {
		add_submenu_page( 'biab-plugin', __( 'Camera', 'bloginbox' ), __( 'Camera', 'bloginbox' ), 'manage_options', 'biab-plugin-camera', array( $this, 'show_page' ) );
	}

	public function wp_head() {
		wp_enqueue_style( 'bloginbox', plugin_dir_url( BIAB_FILE ).'plugin.css' );
	}

	public function set_schedule() {
		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'biab_camera-schedule' ) ) {
			$this->cron->set( intval( $_POST['interval'], 10 ) );

			wp_safe_redirect( admin_url( 'admin.php?page=biab-plugin-camera&msg=saved' ) );
		}
	}

	public function save_settings() {
		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'biab_camera-settings' ) ) {
			$settings = new CameraSettings();
			$settings->save( $_POST );

			wp_safe_redirect( admin_url( 'admin.php?page=biab-plugin-camera&msg=saved&sub=settings' ) );
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
		$interval = $this->cron->get_interval();
?>
	<h2 class="subsubsubheader"><?php _e( 'Camera Control', 'bloginbox' ); ?></h2>
	<p><?php _e( 'With an attached <a target="_blank" href="https://www.raspberrypi.org/products/camera-module/">camera module</a> you can take a photo and have it automatically added to a new blog post.', 'bloginbox' ); ?></p>

	<h3><?php _e( 'Manual Photo', 'bloginbox' ); ?></h3>
	<p><?php _e( 'Take a photo right now by clicking this button.', 'bloginbox' ); ?></p>
	<button class="button" id="submit-btn"><?php _e( 'Take Photo', 'bloginbox' ); ?></button>

	<h3><?php _e( 'Scheduled Photo', 'bloginbox' ); ?></h3>
	<p><?php _e( 'Take a photo on a schedule by setting the number of seconds between each photo', 'bloginbox' ); ?>:</p>

	<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
		<p><label><input type="number" name="interval" value="<?php echo esc_attr( $interval ); ?>"/> <?php _e( 'photo interval (seconds)', 'bloginbox' ); ?></label></p>

		<?php submit_button( false, 'small' ); ?>

		<input type="hidden" name="action" value="biab_camera_schedule" />
		<?php wp_nonce_field( 'biab_camera-schedule' ); ?>
	</form>

	<h3><?php _e( 'Externally Triggered Photo', 'bloginbox' ); ?></h3>
	<p><?php _e( 'Trigger a photo externally by hooking your trigger to the command <code>XXXX</code>.', 'bloginbox' ); ?></p>
	<p><?php _e( 'For example:', 'bloginbox' ); ?></p>
	<pre>
/opt/thing/XXXX &lt;<?php _e( 'title', 'bloginbox' ); ?>&gt;
	</pre>
<?php
	}

	private function show_page_settings() {
		$settings = new CameraSettings();

?>
	<h2 class="subsubsubheader"><?php _e( 'Camera Settings', 'bloginbox' ); ?></h2>

	<p>These settings affect the image taken by the camera. Changing a value will update the image preview.</p>

	<form method="POST" action="<?php echo admin_url( 'admin-post.php' ); ?>">
		<table class="form-table">
			<tr>
				<th>Vertical flip</th>
				<td><input type="checkbox" name="vertical" <?php checked( $settings->is_enabled( 'vflip' ) ); ?>/></td>
			</tr>
			<tr>
				<th>Horizontal flip</th>
				<td><input type="checkbox" name="horizontal" <?php checked( $settings->is_enabled( 'hflip' ) ); ?>/></td>
			</tr>
			<tr>
				<th>Quality</th>
				<td><input type="number" name="quality" min="0" max="100" step="1" value="<?php echo esc_attr( $settings->get_value( 'quality' ) ); ?>"/> 0 to 100</td>
			</tr>
			<tr>
				<th>Brightness</th>
				<td><input type="number" name="brightness" min="0" max="100" step="1" value="<?php echo esc_attr( $settings->get_value( 'brightness' ) ); ?>"/> 0 to 100</td>
			</tr>
			<tr>
				<th>Saturation</th>
				<td><input type="number" name="saturation" min="-100" max="100" step="1" value="<?php echo esc_attr( $settings->get_value( 'saturation' ) ); ?>"/> -100 to 100</td>
			</tr>
			<tr>
				<th>Sharpness</th>
				<td><input type="number" name="sharpness" min="-100" max="100" step="1" value="<?php echo esc_attr( $settings->get_value( 'sharpness' ) ); ?>"/> -100 to 100</td>
			</tr>
			<tr>
				<th>Contrast</th>
				<td><input type="number" name="contrast" min="-100" max="100" step="1" value="<?php echo esc_attr( $settings->get_value( 'contrast' ) ); ?>"/> -100 to 100</td>
			</tr>
			<tr>
				<th>Effect</th>
				<td>
					<select name="effect">
						<?php foreach ( $settings->get_effects() as $name => $title ) : ?>
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
// 	<script>
// 		function take_photo() {
// 			jQuery('#submit-btn').hide();
// 			jQuery('#loading-gif').show();
// 			jQuery.ajax({
// 				type: "POST",
// 				url: "admin-post.php",
// 				data: { action: 'biab_take_photo' },
// 				dataType: 'json',
// 				success: function( data ) {
// 					jQuery('#loading-gif').hide();
// 					jQuery('#submit-btn').show();
// 					jQuery('#result').html("<div style='padding:8px 0'><a href='"+data.post_url+"'><img src='"+data.photo_url+"' width='256'></a></div>");
// 				}
// 			} );
// 			return false; // so page doesn't refresh
// 		}
// 	</script>
// <?php
// 	}
}

add_action( 'init', array( 'BiabCamera', 'init' ), 11 );
