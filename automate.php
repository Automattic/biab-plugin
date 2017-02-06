<?php

add_filter( 'cron_schedules', 'biab_cron_schedule' );
add_action( 'biab_photo', 'biab_photo' );

function biab_photo() {
	exec( '/opt/wp/photo.sh' );
}

function biab_cron_schedule( $schedules ) {
	$interval = get_option( 'biab_interval' );

	if ( $interval > 0 ) {
		$schedules['biab'] = array(
			'interval' => $interval,
			'display'  => esc_html__( 'BIAB' ),
		);
	}

	return $schedules;
}

function disable_cron() {
	wp_unschedule_event( wp_next_scheduled( 'biab_photo' ), 'biab_photo' );
}

function enable_cron( $interval ) {
	$next = wp_next_scheduled( 'biab_photo' );

	if ( $next ) {
		disable_cron();
	}

	wp_schedule_event( time() + $interval, 'biab', 'biab_photo' );
}

function biab_automate() {
	$interval = get_option( 'biab_interval' );

	if ( isset( $_POST['interval'] ) && check_admin_referer( 'biab_automate' ) ) {
		$interval = intval( $_POST['interval'], 10 );

		if ( $interval === 0 ) {
			disable_cron();
		} else {
			enable_cron( $interval );
		}

		update_option( 'biab_interval', $interval );
	}

	if ( $interval <= 0 ) {
		$interval = 0;
	}

?>
	<div style="padding:32px">
    <div style="display:flex">
        <div style="margin-right:32px">
            <img src="<?php echo plugins_url( 'wapi-512.png', __FILE__ ); ?>" width="128">
        </div>
		<div>
	        <h1>Automate Blog In A Box</h1>

			<form method="post">
				<label><input type="number" name="interval" value="<?php echo esc_attr( $interval ); ?>"/> Interval (sec)</label>
				<input type="submit" value="Save"/>

				<?php wp_nonce_field( 'biab_automate' ); ?>
			</form>

			<div id="result"></div>
		</div>
    </div>
<?php
}
