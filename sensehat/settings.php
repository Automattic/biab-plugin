<?php

class SensehatSettings {
	const OPTION_INTERVAL = 'biab_sensehat_interval';
	const OPTION_PERIOD = 'biab_sensehat_period';
	const OPTION_UNITS = 'biab_sensehat_units';
	const OPTION_DISPLAY = 'biab_sensehat_display';

	public function set_interval( $interval ) {
		$interval = max( 0, $interval );

		update_option( self::OPTION_INTERVAL, $interval );
	}

	public function set_period( $period ) {
		update_option( self::OPTION_PERIOD, $period );
	}

	public function set_display( $onoff ) {
		update_option( self::OPTION_DISPLAY, $onoff ? 'on' : 'off' );
	}

	public function set_units( $units ) {
		if ( $units === 'celsius' || $units === 'fahrenheit' ) {
			update_option( self::OPTION_UNITS, $units );
		}
	}

	public function get_interval() {
		return intval( get_option( self::OPTION_INTERVAL ), 10 );
	}

	public function get_period() {
		return get_option( self::OPTION_PERIOD );
	}

	public function get_display() {
		$display = get_option( self::OPTION_DISPLAY );

		if ( ! $display || $display === 'on' ) {
			return true;
		}

		return false;
	}

	public function get_units() {
		$units = get_option( self::OPTION_UNITS );
		if ( $units ) {
			return $units;
		}

		return 'celsius';
	}

	public function get_periods() {
		return array(
			'minute' => __( 'Minutes', 'bloginbox' ),
			'hour'   => __( 'Hours', 'bloginbox' ),
			'day'    => __( 'Days', 'bloginbox' ),
		);
	}
}
