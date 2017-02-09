<?php

class SensehatControl extends BiabControl {
	public function set_schedule( $interval, $period ) {
		$result = $this->request( 'sensehat-schedule', intval( $interval, 10 ).' '.$period );

		if ( $result['retval'] === 0 ) {
			return true;
		}

		return false;
	}
}
