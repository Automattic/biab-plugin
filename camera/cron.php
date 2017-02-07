<?php

class CameraCron {
	const OPTION_NAME = 'biab_camera_interval';

	public function set( $interval ) {
		$interval = max( 0, $interval );

		update_option( self::OPTION_NAME, $interval );
	}

	public function get() {
		return intval( get_option( self::OPTION_NAME ), 10 );
	}
}
