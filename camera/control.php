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
		$validate = new CameraSettings();

		$cmd = array(
			'-q '.intval( $settings['quality'], 10 ),
			'--sharpness '.intval( $settings['sharpness'], 10 ),
			'--contrast '.intval( $settings['contrast'], 10 ),
			'--brightness '.intval( $settings['brightness'], 10 ),
			'--saturation '.intval( $settings['saturation'], 10 ),
		);

		if ( $settings['vflip'] ) {
			$cmd[] = '-vf';
		}

		if ( $settings['hflip'] ) {
			$cmd[] = '-hf';
		}

		if ( $settings['ifx'] && isset( $validate->get_effect_values()[ $settings['ifx'] ] ) ) {
			$cmd[] = '--imxfx '.$settings['ifx'];
		}

		if ( $settings['awb'] !== 'auto' && isset( $validate->get_awb_values()[ $settings['awb'] ] ) ) {
			$cmd[] = '--awb '.$settings['awb'];
		}

		if ( $settings['iso'] !== 100 ) {
			$cmd[] = '--iso '.intval( $settings['iso'], 10 );
		}

		return $this->has_no_error( $this->request( 'camera-settings', implode( ' ', $cmd ) ) );
	}
}
