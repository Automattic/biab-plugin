<?php

class CameraCron {
	const OPTION_INTERVAL = 'biab_camera_interval';
	const OPTION_PERIOD = 'biab_camera_period';

	public function set_interval( $interval ) {
		$interval = max( 0, $interval );

		update_option( self::OPTION_INTERVAL, $interval );
	}

	public function set_period( $period ) {
		update_option( self::OPTION_PERIOD, $period );
	}

	public function get_interval() {
		return intval( get_option( self::OPTION_INTERVAL ), 10 );
	}

	public function get_period() {
		return get_option( self::OPTION_PERIOD );
	}

	public function get_periods() {
		return array(
			'minute' => __( 'Minutes', 'bloginbox' ),
			'hour'   => __( 'Hours', 'bloginbox' ),
			'day'    => __( 'Days', 'bloginbox' ),
		);
	}
}
