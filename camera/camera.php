<?php

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
		}
	}

	public function admin_menu() {
		add_submenu_page( 'biab-plugin', __( 'Camera', 'bloginbox' ), __( 'Camera', 'bloginbox' ), 'manage_options', 'biab-plugin-camera', array( $this, 'show_page' ) );
	}

	public function show_page() {
		?>
	    <div style="display:flex; padding-top: 32px">
	        <div style="margin-right:32px">
	            <img src="<?php echo plugins_url( 'wapi-512.png', BIAB_FILE ); ?>" width="128">
	        </div>
			<div>
		        <h1> Put Your Blog in a Box </h1>

				<form>
					<button class="button" id="submit-btn" onClick="return take_photo()"> Take Photo </button>
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

	public function take_photo() {
		$output = array();

		exec( "/opt/wp/photo.sh", $output );

		echo implode($output);
	}
}

add_action( 'init', array( 'BiabCamera', 'init' ), 11 );
