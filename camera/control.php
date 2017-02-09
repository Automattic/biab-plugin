<?php

class CameraControl extends BiabControl {
	public function take_snapshot() {
		return $this->has_no_error( $this->request( 'camera-snapshot' ) );
	}

	public function take_photo() {
		$result = $this->request( 'camera-take-photo' );

		if ( $result['retval'] === 0 ) {
			$json = json_decode( $result['output'][0] );

			if ( !isset( $json->error ) ) {
				return intval( $json->id, 10 );
			}
		}

		return false;
	}

	public function set_schedule( $interval, $period ) {
		return $this->has_no_error( $this->request( 'camera-schedule', intval( $interval, 10 ).' '.$period ) );
	}

	public function save_settings( $settings ) {
		$cmd = array(
			'-q '.$settings['quality'],
			'--sharpness '.$settings['sharpness'],
			'--contrast '.$settings['contrast'],
			'--brightness '.$settings['brightness'],
			'--saturation '.$settings['saturation'],
		);

		if ( $settings['vflip'] ) {
			$cmd[] = '-vf';
		}

		if ( $settings['hflip'] ) {
			$cmd[] = '-hf';
		}

		if ( $settings['ifx'] ) {
			$cmd[] = '--imxfx '.$settings['ifx'];
		}

		if ( $settings['awb'] !== 'auto' ) {
			$cmd[] = '--awb '.$settings['awb'];
		}

		if ( $settings['iso'] !== 100 ) {
			$cmd[] = '--iso '.$settings['iso'];
		}

		return $this->has_no_error( $this->request( 'camera-settings', implode( ' ', $cmd ) ) );
	}
}
