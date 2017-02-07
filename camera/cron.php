<?php

class CameraCron {
	public function __construct() {
		add_filter( 'cron_schedules', array( $this, 'cron_schedule' ) );
	}

	public function set( $interval ) {
		$interval = max( 0, $interval );

		update_option( 'biab_camera_interval', $interval );

		if ( $interval === 0 ) {
			return $this->disable();
		}

		return $this->enable();
	}

	private function disable() {
		wp_unschedule_event( wp_next_scheduled( 'biab_photo' ), array( $this, 'take_photo' ) );
	}

	private function enable( $interval ) {
		$next = wp_next_scheduled( 'biab_photo' );

		if ( $next ) {
			$this->disable_cron();
		}

		wp_schedule_event( time() + $interval, 'biab', array( $this, 'take_photo' ) );
	}

	public function get_interval() {
		return intval( get_option( 'biab_camera_interval' ), 10 );
	}

	public function cron_schedule( $schedules ) {
		$interval = $this->get_interval();

		if ( $interval > 0 ) {
			$schedules['biab'] = array(
				'interval' => $interval,
				'display'  => esc_html__( 'BIAB' ),
			);
		}

		return $schedules;
	}
}
