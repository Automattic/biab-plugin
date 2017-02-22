<?php

class SensehatControl extends BiabControl {
	public function set_options( $interval, $period, $display, $units, $report ) {
		$str = sprintf( '%d %s %s %s %s', intval( $interval, 10 ), $period, ( $display ? 'on' : 'off' ), $units, $report );

		return $this->has_no_error( $this->request( 'sensehat-settings', $str ) );
	}

	public function publish_report( ){
		$result = $this->request( 'sensehat-report' );

		if ( $result['retval'] === 0 ) {
			$json = json_decode( $result['output'][0] );

			if ( !isset( $json->error ) ) {
				return intval( $json->id, 10 );
			}
		}

		return false;
	}

}
