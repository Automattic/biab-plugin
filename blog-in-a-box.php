<?php
/*
Plugin Name: Blog In A Box
Plugin URI: https://github.com/Automattic/blog-in-a-box/
Description: Connect Raspberry Pi devices to WordPress
Author: Automattic
Author URI: https://automattic.com
Text domain: bloginbox
Domain path: /locale
Version: 0.1
*/

define( 'BIAB_FILE', __FILE__ );

include dirname( BIAB_FILE ).'/control.php';

function biab_is_module_enabled( $module ) {
	$modules = get_option( 'biab_modules' );

	if ( is_array( $modules ) && isset( $modules[$module] ) && $modules[$module] ) {
		return true;
	}

	return false;
}

class BiabModules extends BiabControl {
	public function set_modules( $modules ) {
		$device = array(
			join( ',', array_keys( array_filter( $modules ) ) ),
			get_home_path(),
			get_rest_url(),
		);

		return $this->has_no_error( $this->request( 'devices', join( ' ' , $device ) ) );
	}
}

class BlogInBox {
	private static $instance = null;

	static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new BlogInBox();

			load_plugin_textdomain( 'bloginbox', false, dirname( plugin_basename( BIAB_FILE ) ).'/locale/' );
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_post_biab_modules', array( $this, 'save_modules' ) );
		add_action( 'toplevel_page_biab-plugin', array( $this, 'wp_head' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_sensehat_libs' ) );
		add_action( 'customize_preview_init', array( $this, 'add_sensehat_libs') );
	}

	public function admin_menu() {
		add_menu_page( __( 'Blog in a Box', 'bloginbox' ), __( 'Blog In A Box', 'bloginbox' ), 'manage_options', 'biab-plugin', array( $this, 'show_page' ), 'data:image/svg+xml;base64,'. base64_encode( file_get_contents( dirname( __FILE__ ) . '/dashicon.svg' ) ) );
	}

	public function wp_head() {
		wp_enqueue_style( 'bloginbox', plugin_dir_url( BIAB_FILE ).'plugin.css' );
	}

	public function add_sensehat_libs() {
		// this lib is used to create the weather reports
		wp_enqueue_script( 'bloginbox-d3', plugin_dir_url( BIAB_FILE ).'sensehat/js/d3.v4.min.js', [], 'v4' );
		wp_enqueue_script( 'bloginbox-sensehat-chart', plugin_dir_url( BIAB_FILE ).'sensehat/js/sensehat-report.js', ['bloginbox-d3'], '170220' );
	}

	public function save_modules() {
		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'biab_modules-save' ) ) {
			$modules = $this->get_available_modules();
			$enabled = array();

			foreach ( array_keys( $modules ) as $module ) {
				$enabled[$module] = isset( $_POST[$module] ) ? true : false;
			}

			update_option( 'biab_modules', $enabled );

			$control = new BiabControl();
			$control->set_path( $_POST['path'] );

			$modules = new BiabModules();
			if ( $modules->set_modules( $enabled ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=biab-plugin&msg=saved' ) );
			} else {
				wp_safe_redirect( admin_url( 'admin.php?page=biab-plugin&msg=savefail' ) );
			}
		}
	}

	private function get_available_modules() {
		return array(
			'camera'   => __( 'Camera', 'bloginbox' ),
			'sensehat' => __( 'Sense Hat', 'bloginbox' ),
		);
	}

	public function show_page() {
		$control = new BiabControl();
		$modules = $this->get_available_modules();
?>
<div class="wrap">
	<?php if ( isset( $_GET['msg'] ) && $_GET['msg'] === 'saved' ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'Saved!', 'bloginbox' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( isset( $_GET['msg'] ) && $_GET['msg'] === 'savefail' ) : ?>
		<div class="notice notice-error">
			<p><?php _e( 'Failed to update settings', 'bloginbox' ); ?></p>
		</div>
	<?php endif; ?>

	<h1><?php _e( 'Blog In A Box', 'bloginbox' ); ?></h1>

	<div class="biab-wapuu">
		<img src="<?php echo plugins_url( 'wapi-512.png', BIAB_FILE ); ?>" width="128" />
	</div>

	<p><?php _e( 'Quickly and easily setup a WordPress blog on a Raspberry Pi, and provide a means to combine it with sensors.', 'bloginbox' ); ?></p>

	<h3><?php _e( 'Configuration', 'bloginbox' ); ?></h3>
	<form method="POST" action="<?php echo admin_url( 'admin-post.php' ); ?>">
		<h4><?php _e( 'Modules', 'bloginbox' ); ?></h4>
		<ul>
			<?php foreach ( $modules as $module => $title ) : ?>
				<li>
					<label>
						<input type="checkbox" name="<?php echo esc_attr( $module ); ?>" <?php checked( biab_is_module_enabled( $module ) ); ?>/>

						<?php echo esc_html( $title ); ?>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>

		<h4><?php _e( 'Plugin Setup', 'bloginbox' ); ?></h4>
		<?php _e( 'Plugin control path', 'bloginbox' ); ?> <input type="text" name="path" value="<?php echo esc_attr( $control->get_path() ); ?>"/> <?php _e( "The full path to the plugin's control companion", 'bloginbox' ); ?>

 		<input type="hidden" name="action" value="biab_modules" />
		<?php wp_nonce_field( 'biab_modules-save' ); ?>
		<?php submit_button(); ?>
	</form>
</div>
<?php
	}
}

add_action( 'init', array( 'BlogInBox', 'init' ) );

// Load modules
include dirname( BIAB_FILE ).'/camera/camera.php';
include dirname( BIAB_FILE ).'/sensehat/sensehat.php';
