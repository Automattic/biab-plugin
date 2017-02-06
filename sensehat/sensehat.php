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
		}

		register_uninstall_hook( BIAB_FILE, array( 'Redirection_Admin', 'plugin_uninstall' ) );
	}

	public function widgets_init() {
		add_action( 'widgets_init', array( $this, 'register_airpressure_widget' ) );
		add_action( 'widgets_init', array( $this, 'register_temperature_widget' ) );
		add_action( 'widgets_init', array( $this, 'register_humidity_widget' ) );
	}

	public function admin_menu() {
		add_submenu_page( 'biab-plugin', __( 'Sense Hat', 'bloginbox' ), __( 'Sense Hat', 'bloginbox' ), 'manage_options', 'biab-plugin-sensehat', array( $this, 'show_page' ) );
	}

	public static function plugin_activated() {
		$this->create_table();
	}

	private function create_table() {
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
		HERE
		<?php
	}

	public function register_temperature_widget() {
		require_once dirname( __FILE__ ).'/widgets/SenseHat_Temperature_Widget.php';

		register_widget( 'SenseHat_Temperature_Widget' );
	}

	public function register_humidity_widget() {
		require_once dirname( __FILE__ ).'/widgets/SenseHat_Humidity_Widget.php';

		register_widget( 'SenseHat_Humidity_Widget' );
	}

	public function register_airpressure_widget() {
		require_once dirname( __FILE__ ).'/widgets/SenseHat_AirPressure_Widget.php';

		register_widget( 'SenseHat_AirPressure_Widget' );
	}
}

add_action( 'init', array( 'BiabSensehat', 'init' ), 11 );
add_action( 'widgets_init', array( 'BiabSensehat', 'widgets_init' ) );

register_activation_hook( BIAB_FILE, array( 'BiabSensehat', 'plugin_activated' ) );
