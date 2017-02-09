<?php

class SensehatControl extends BiabControl {
	public function set_schedule( $interval, $period ) {
		return $this->has_no_error( $this->request( 'sensehat-schedule', intval( $interval, 10 ).' '.$period ) );
	}
}
