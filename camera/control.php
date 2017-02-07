<?php

class CameraControl extends BiabControl {
	const DEVICE = 'camera';

	public function take_photo() {

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

		$result = $this->request( self::DEVICE, 'settings', implode( ' ', $cmd ) );
		if ( $result['retval'] === 0 ) {
			return true;
		}

		return false;
	}
}
