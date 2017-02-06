<?php
/*
Plugin Name: Blog in a Box
Description: A plugin to help Selma publish photos of her artwork to her grandparents.
Author: Automattic
Version: 0.1
*/

include dirname( __FILE__ ).'/automate.php';

add_action('admin_menu', 'biab_setup_menu');

function biab_setup_menu() {
	add_menu_page( 'Blog in a Box Admin Page', 'BIAB 📦', 'manage_options', 'biab-plugin', 'biab_init' );
	add_submenu_page( 'biab-plugin', 'Automate BIAB', 'Automate', 'manage_options', 'biab-plugin-automate', 'biab_automate' );
}

function biab_init() { ?>
	<div style="padding:32px">
    <div style="display:flex">
        <div style="margin-right:32px">
            <img src="<?php echo plugins_url( 'wapi-512.png', __FILE__ ); ?>" width="128">
        </div>
		<div>
	        <h1> Put Your Blog in a Box </h1>

			<form>
				<button id="submit-btn" onClick="return take_photo()"> Take Photo </button>
				<img style="display:none" src="/i/loading.gif" id="loading-gif">
			</form>

			<div id="result"></div>
		</div>
    </div>
	<script>
		function take_photo() {
			jQuery('#submit-btn').hide();
			jQuery('#loading-gif').show();
			jQuery.ajax({
				type: "POST",
				url: "admin-post.php",
				data: { action: 'biab_take_photo' },
				dataType: 'json',
				success: function( data ) {
					jQuery('#loading-gif').hide();
					jQuery('#submit-btn').show();
					jQuery('#result').html("<div style='padding:8px 0'><a href='"+data.post_url+"'><img src='"+data.photo_url+"' width='256'></a></div>");
				}
			} );
			return false; // so page doesn't refresh
		}
	</script>
<?php
}
add_action( 'admin_post_biab_take_photo', 'biab_take_photo' );

function biab_take_photo() {
	error_log("here!");
	$output = array();
	exec("/opt/wp/photo.sh", $output);
	error_log( print_r( $output, true ) );
	echo implode($output);
}

function sense_hat_create_table() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'sense_hat';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		temperature double NOT NULL,
		humidity double NOT NULL,
		air_pressure double NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

}
register_activation_hook( __FILE__, 'sense_hat_create_table' );

function register_sensehat_temperature_widget() {
	require_once( ABSPATH . 'wp-content/plugins/biab/widgets/SenseHat_Temperature_Widget.php' );
    register_widget( 'SenseHat_Temperature_Widget' );
}
add_action( 'widgets_init', 'register_sensehat_temperature_widget' );
