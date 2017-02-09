<?php

class CameraControl extends BiabControl {
	public function take_photo() {
		$result = $this->request( 'takephoto' );

		if ( $result['retval'] === 0 ) {
			$json = json_decode( $result['output'][0] );

			if ( !isset( $json->error ) ) {
				return intval( $json->id, 10 );
			}
		}

		return false;
	}

	public function set_schedule( $interval, $period ) {
		$result = $this->request( 'cameraschedule', intval( $interval, 10 ).' '.$period );

		if ( $result['retval'] === 0 ) {
			return true;
		}

		return false;
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

		$result = $this->request( 'camerasettings', implode( ' ', $cmd ) );
		if ( $result['retval'] === 0 ) {
			return true;
		}

		return false;
	}
}
