<?php

include dirname( __FILE__ ).'/cron.php';
include dirname( __FILE__ ).'/control.php';

class BiabSensehat {
	private static $instance = null;

	static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new BiabSensehat();
		}

		return self::$instance;
	}

	public function __construct() {
		$enabled = biab_is_module_enabled( 'sensehat' );

		if ( $enabled ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'blog-in-a-box_page_biab-plugin-sensehat', array( $this, 'wp_head' ) );
			add_action( 'admin_post_biab_sensehat_schedule', array( $this, 'set_schedule' ) );
		}
	}

	public function admin_menu() {
		add_submenu_page( 'biab-plugin', __( 'Sense Hat', 'bloginbox' ), __( 'Sense Hat', 'bloginbox' ), 'manage_options', 'biab-plugin-sensehat', array( $this, 'show_page' ) );
	}

	public function wp_head() {
		wp_enqueue_style( 'bloginbox', plugin_dir_url( BIAB_FILE ).'plugin.css' );
	}

	public static function plugin_activated() {
		require_once dirname( __FILE__ ).'/database.php';

		$database = new Sensehat_Database();
		$database->create();
	}

	public static function plugin_uninstall() {
		require_once dirname( __FILE__ ).'/database.php';

		$database = new Sensehat_Database();
		$database->remove();
	}

	public function set_schedule() {
		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'biab_sensehat-schedule' ) ) {
			$cron = new SensehatCron();
			$cron->set_interval( intval( $_POST['interval'], 10 ) );
			$cron->set_period( $_POST['period'] );

			$control = new SensehatControl();
			if ( $control->set_schedule( $cron->get_interval(), $cron->get_period() ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=biab-plugin-sensehat&msg=saved' ) );
			} else {
				wp_safe_redirect( admin_url( 'admin.php?page=biab-plugin-sensehat&msg=savefail' ) );
			}
		}
	}

	public function show_page() {
		$cron = new SensehatCron();
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

			<h1><?php _e( 'Blog In A Box &ndash; Sense HAT', 'bloginbox' ); ?></h1>

			<div class="biab-wapuu">
				<img src="<?php echo plugins_url( 'wapi-512.png', BIAB_FILE ); ?>" width="128" />
			</div>

			<p><?php _e( 'With an attached <a target="_blank" href="https://www.raspberrypi.org/products/sense-hat/">Sense HAT add-on board</a> you can measure the temperature, humidity, and air pressure to show their values as widgets in your site.', 'bloginbox' ); ?></p>

			<h3><?php _e( 'Scheduled Reading', 'bloginbox' ); ?></h3>
			<p><?php _e( 'Take a reading on a schedule by setting a period between each reading.', 'bloginbox' ); ?>:</p>

			<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
				<p>
					<input type="number" name="interval" style="width: 50px" value="<?php echo esc_attr( $cron->get_interval() ); ?>"/>
					<select name="period">
						<?php foreach ( $cron->get_periods() as $key => $name ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $cron->get_period(), $key ) ?>><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>

				<?php submit_button(); ?>

				<input type="hidden" name="action" value="biab_sensehat_schedule" />
				<?php wp_nonce_field( 'biab_sensehat-schedule' ); ?>
			</form>
		</div>
		<?php
	}

	public static function register_temperature_widget() {
		require_once dirname( __FILE__ ).'/widgets/SenseHat_Temperature_Widget.php';

		register_widget( 'SenseHat_Temperature_Widget' );
	}

	public static function register_humidity_widget() {
		require_once dirname( __FILE__ ).'/widgets/SenseHat_Humidity_Widget.php';

		register_widget( 'SenseHat_Humidity_Widget' );
	}

	public static function register_airpressure_widget() {
		require_once dirname( __FILE__ ).'/widgets/SenseHat_AirPressure_Widget.php';

		register_widget( 'SenseHat_AirPressure_Widget' );
	}

}

add_action( 'init', array( 'BiabSensehat', 'init' ), 11 );

if ( biab_is_module_enabled( 'sensehat' ) ) {
	add_action( 'widgets_init', array( 'BiabSensehat', 'register_airpressure_widget' ) );
	add_action( 'widgets_init', array( 'BiabSensehat', 'register_humidity_widget' ) );
	add_action( 'widgets_init', array( 'BiabSensehat', 'register_temperature_widget' ) );
}

register_activation_hook( BIAB_FILE, array( 'BiabSensehat', 'plugin_activated' ) );
register_uninstall_hook( BIAB_FILE, array( 'BiabSensehat', 'plugin_uninstall' ) );
