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

function biab_is_module_enabled( $module ) {
	$modules = get_option( 'biab_modules' );

	if ( is_array( $modules ) && isset( $modules[$module] ) && $modules[$module] ) {
		return true;
	}

	return false;
}

if ( !function_exists( 'pr' ) ) {
	function pr( $things ) {
		echo '<pre>';
		print_r( $things );
		echo '</pre>';
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
	}

	public function admin_menu() {
		add_menu_page( __( 'Blog in a Box', 'bloginbox' ), __( 'Blog In A Box', 'bloginbox' ), 'manage_options', 'biab-plugin', array( $this, 'show_page' ), 'dashicons-archive' );
	}

	public function wp_head() {
		wp_enqueue_style( 'bloginbox', plugin_dir_url( BIAB_FILE ).'plugin.css' );
	}

	public function save_modules() {
		if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'modules_save' ) ) {
			$modules = $this->get_available_modules();
			$enabled = array();

			foreach ( $modules AS $module => $title ) {
				$enabled[$module] = isset( $_POST[$module] ) ? true : false;
			}

			update_option( 'biab_modules', $enabled );
			wp_safe_redirect( admin_url( 'admin.php?page=biab-plugin&msg=saved' ) );
		}
	}

	private function get_available_modules() {
		return array(
			'camera'   => __( 'Camera', 'bloginbox' ),
			'sensehat' => __( 'Sense Hat', 'bloginbox' ),
		);
	}

	public function show_page() {
		$modules = $this->get_available_modules();

 		if ( isset( $_GET['msg'] ) && $_GET['msg'] === 'saved' ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'Modules have been saved', 'bloginbox' ); ?></p>
		</div>
		<?php endif; ?>

<div class="wrap">
	<h2><?php _e( 'Blog In A Box', 'bloginbox' ); ?></h2>

	<div class="biab-wapuu">
		<img src="<?php echo plugins_url( 'wapi-512.png', BIAB_FILE ); ?>" width="128" />
	</div>

	<p><?php _e( 'Quickly and easily setup a WordPress blog on a Raspberry Pi, and provide a means to combine it with sensors.', 'bloginbox' ); ?></p>

	<h3><?php _e( 'Sensor Modules', 'bloginbox' ); ?></h3>
	<form method="POST" action="<?php echo admin_url( 'admin-post.php' ); ?>">
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

 		<input type="hidden" name="action" value="biab_modules" />
		<?php wp_nonce_field( 'modules_save' ); ?>
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
