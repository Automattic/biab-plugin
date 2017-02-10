<?php

class SensehatControl extends BiabControl {
	public function set_options( $interval, $period, $display, $units ) {
		$str = sprintf( '%d %s %s %s', intval( $interval, 10 ), $period, ( $display ? 'on' : 'off' ), $units );

		return $this->has_no_error( $this->request( 'sensehat-settings', $str ) );
	}
}
