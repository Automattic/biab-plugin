<?php

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

	public function show_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Blog In A Box &ndash; Sense HAT', 'bloginbox' ); ?></h1>

			<div class="biab-wapuu">
				<img src="<?php echo plugins_url( 'wapi-512.png', BIAB_FILE ); ?>" width="128" />
			</div>

			<h2 class="subsubsubheader"><?php _e( 'Sense HAT Control', 'bloginbox' ); ?></h2>
			<p><?php _e( 'With an attached <a target="_blank" href="https://www.raspberrypi.org/products/sense-hat/">Sense HAT add-on board</a> you can measure the temperature, humidity, and air pressure to show their values as widgets in your site.', 'bloginbox' ); ?></p>

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
