<?php

include dirname( __FILE__ ).'/settings.php';
include dirname( __FILE__ ).'/control.php';
include dirname( __FILE__ ).'/rest.php';

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
			add_action( 'admin_post_biab_sensehat_options', array( $this, 'set_options' ) );
			add_action( 'admin_post_biab_publish_report', array( $this, 'publish_report' ) );
			add_shortcode( 'sensehat', array( $this, 'sensehat_shortcode_to_graph' ) );
		}
	}

	public function publish_report( $atts ) {
		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'biab_sensehat-publish_report' ) ) {
			$control = new SensehatControl();
			$result = $control->publish_report();

			if ( $result !== false ) {
				echo json_encode( array(
					'post_id' => $result,
					'url' => get_post_permalink( $result ),
					) );
				return;
			}

			echo json_encode( array( 'error' => true ) );
		}
	}

	public function sensehat_shortcode_to_graph( $atts ) {
		$svg_id = uniqid();
		$settings = new SensehatSettings();
		$units = $settings->get_units();
		$temperature = $units ? $units : 'celsius';
		$label = 'Temperature (°'.strtoupper($temperature[0]).')';
		$api_route = rest_url().BiabSenseHAT_REST::API_NAMESPACE.BiabSenseHAT_REST::API_ROUTE."?before=".$atts['before']."&after=".$atts['after']."&temperature=".$temperature;
		$settings = $control = new SensehatSettings();
		return '<svg id="'.$svg_id.'" width="480" height="250"></svg>
			<script>SenseHatChart("'.$api_route.'", "'.$svg_id.'", "'.$label.'");</script>';
	}

	public function admin_menu() {
		add_submenu_page( 'biab-plugin', __( 'Sense Hat', 'bloginbox' ), __( 'Sense Hat', 'bloginbox' ), 'manage_options', 'biab-plugin-sensehat', array( $this, 'show_page' ) );
	}

	public function wp_head() {
		wp_enqueue_style( 'bloginbox', plugin_dir_url( BIAB_FILE ).'plugin.css' );
		wp_enqueue_script( 'biab-sensehat', plugin_dir_url( __FILE__ ).'js/sensehat.js', 'jquery' );
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

	public function set_options() {
		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'biab_sensehat-options' ) ) {
			$settings = new SensehatSettings();
			$settings->set_interval( intval( $_POST['interval'], 10 ) );
			$settings->set_period( $_POST['period'] );
			$settings->set_display( isset( $_POST['display'] ) );
			$settings->set_units( $_POST['units'] );

			$control = new SensehatControl();
			if ( $control->set_options( $settings->get_interval(), $settings->get_period(), $settings->get_display(), $settings->get_units() ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=biab-plugin-sensehat&msg=saved' ) );
			} else {
				wp_safe_redirect( admin_url( 'admin.php?page=biab-plugin-sensehat&msg=savefail' ) );
			}
		}
	}

	public function show_page() {
		$settings = new SensehatSettings();

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

			<h3><?php _e( 'Settings', 'bloginbox' ); ?></h3>

			<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
				<table class="form-table">
					<tr>
						<th><?php _e( 'Reading frequency', 'bloginbox' ); ?></th>
						<td
						<p>
							<input type="number" name="interval" style="width: 50px" value="<?php echo esc_attr( $settings->get_interval() ); ?>"/>
							<select name="period">
								<?php foreach ( $settings->get_periods() as $key => $name ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $settings->get_period(), $key ) ?>><?php echo esc_html( $name ); ?></option>
								<?php endforeach; ?>
							</select>
						</p>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Display', 'bloginbox' ); ?></th>
						<td>
							<p><label><input type="checkbox" name="display" <?php checked( $settings->get_display() ); ?>/> <?php _e( 'enable it for readings and camera activity', 'bloginbox' ); ?></label></p>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Units', 'bloginbox' ); ?></th>
						<td>
							<p>
								<label><input <?php checked( $settings->get_units() === 'celsius' ) ?> type="radio" name="units" value="celsius"/> <?php _e( 'Celsius', 'bloginbox' ); ?></label>
								<label><input <?php checked( $settings->get_units() === 'fahrenheit' ) ?> type="radio" name="units" value="fahrenheit"/> <?php _e( 'Fahrenheit', 'bloginbox' ); ?></label>
							</p>
						</td>
					</tr>
				</table>



				<?php submit_button(); ?>

				<input type="hidden" name="action" value="biab_sensehat_options" />
				<?php wp_nonce_field( 'biab_sensehat-options' ); ?>
			</form>

			<h3><?php _e( 'Manual Temperature Report', 'bloginbox' ); ?></h3>
			<p><?php _e( 'Publish a temperature report with last week values by clicking this button.', 'bloginbox' ); ?></p>
			<form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
				<input type="hidden" name="action" value="biab_publish_report" />
				<?php wp_nonce_field( 'biab_sensehat-publish_report' ); ?>
				<a href="#" title="<?php echo esc_attr( __( 'Publish a report' ) ); ?>" id="publish-report" class="button">Publish report</a>
			</form>

			<p>You can also use the shortcode <code>sensehat</code> in your posts and pages to show any period of time.
			<p><?php _e( 'For example:', 'bloginbox' ); ?></p>
			<p><code><?php _e('[sensehat before="2016-01-02" after="2016-02-02"]')?></code></p>


		</div>
		<?php
	}

	public static function register_temperature_widget() {
		require_once dirname( __FILE__ ).'/widgets/temperature.php';

		register_widget( 'SenseHat_Temperature_Widget' );
	}

	public static function register_humidity_widget() {
		require_once dirname( __FILE__ ).'/widgets/humidity.php';

		register_widget( 'SenseHat_Humidity_Widget' );
	}

	public static function register_airpressure_widget() {
		require_once dirname( __FILE__ ).'/widgets/airpressure.php';

		register_widget( 'SenseHat_AirPressure_Widget' );
	}

}

add_action( 'init', array( 'BiabSensehat', 'init' ), 11 );

if ( biab_is_module_enabled( 'sensehat' ) ) {
	add_action( 'widgets_init', array( 'BiabSensehat', 'register_airpressure_widget' ) );
	add_action( 'widgets_init', array( 'BiabSensehat', 'register_humidity_widget' ) );
	add_action( 'widgets_init', array( 'BiabSensehat', 'register_temperature_widget' ) );
	add_action( 'rest_api_init', array( 'BiabSensehat_REST', 'register_sensehat_routes' ) );
}

register_activation_hook( BIAB_FILE, array( 'BiabSensehat', 'plugin_activated' ) );
register_uninstall_hook( BIAB_FILE, array( 'BiabSensehat', 'plugin_uninstall' ) );
