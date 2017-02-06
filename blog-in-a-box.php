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

include dirname( BIAB_FILE ).'/camera/camera.php';
include dirname( BIAB_FILE ).'/sensehat/sensehat.php';

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
	}

	public function admin_menu() {
		add_menu_page( __( 'Blog in a Box', 'bloginbox' ), __( 'Blog In A Box', 'bloginbox' ), 'manage_options', 'biab-plugin', 'biab_init', 'dashicons-archive' );
	}
}

add_action( 'init', array( 'BlogInBox', 'init' ) );
