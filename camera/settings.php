<?php

class CameraSettings {
	const OPTION_NAME = 'biab_camera_settings';
	private $settings = array();

	public function __construct() {
		$this->settings = $this->get();
	}

	public function get_effect_values() {
		return array(
			''              => __( 'None', 'bloginbox' ),
			'negative'      => __( 'Negative', 'bloginbox' ),
			'solarise'      => __( 'Solarise', 'bloginbox' ),
			'sketch'        => __( 'Sketch', 'bloginbox' ),
			'denoise'       => __( 'Denoise', 'bloginbox' ),
			'emboss'        => __( 'Emboss', 'bloginbox' ),
			'oilpaint'      => __( 'Oil Painting', 'bloginbox' ),
			'hatch'         => __( 'Hatch', 'bloginbox' ),
			'gpen'          => __( 'GPEN', 'bloginbox' ),
			'pastel'        => __( 'Pastel', 'bloginbox' ),
			'watercolour'   => __( 'Watercolour', 'bloginbox' ),
			'film'          => __( 'Film', 'bloginbox' ),
			'blur'          => __( 'Blur', 'bloginbox' ),
			'saturation'    => __( 'Saturation', 'bloginbox' ),
			'colourswap'    => __( 'Colour Swap', 'bloginbox' ),
			'washedout'     => __( 'Washed Out', 'bloginbox' ),
			'posterise'     => __( 'Posterise', 'bloginbox' ),
			'colourpoint'   => __( 'Colour Point', 'bloginbox' ),
			'colourbalance' => __( 'Colour Balance', 'bloginbox' ),
			'cartoon'       => __( 'Cartoon', 'bloginbox' ),
		);
	}

	public function get_iso_values() {
		return array(
			100,
			200,
			320,
			400,
			500,
			640,
			800,
		);
	}

	public function get_awb_values() {
		return array(
			'auto'         => __( 'Auto', 'bloginbox' ),
			'sun'          => __( 'Sunny', 'bloginbox' ),
			'cloud'        => __( 'Cloudy', 'bloginbox' ),
			'shade'        => __( 'Shade', 'bloginbox' ),
			'tungsten'     => __( 'Tungsten lighting', 'bloginbox' ),
			'fluorescent'  => __( 'Fluorescent lighting', 'bloginbox' ),
			'incandescent' => __( 'Incandescent lighting', 'bloginbox' ),
			'flash'        => __( 'Flash', 'bloginbox' ),
			'horizon'      => __( 'Horizon', 'bloginbox' ),
		);
	}

	public function is_enabled( $name ) {
		if ( isset( $this->settings[$name] ) && $this->settings[$name] ) {
			return true;
		}

		return false;
	}

	public function get_value( $name ) {
		if ( isset( $this->settings[$name] ) ) {
			return $this->settings[$name];
		}

		return false;
	}

	public function get() {
		$defaults = array(
			'quality'    => 75,
			'ifx'        => '',
			'vflip'      => false,
			'hflip'      => false,
			'sharpness'  => 0,
			'contrast'   => 0,
			'brightness' => 50,
			'saturation' => 0,
			'iso'        => 100,
			'awb'        => 'auto',
		);

		$settings = get_option( self::OPTION_NAME );

		foreach ( array_keys( $defaults ) as $key ) {
			if ( isset( $settings[$key] ) ) {
				$defaults[$key] = $settings[$key];
			}
		}

		return $defaults;
	}

	public function save( $values ) {
		$quality = intval( $values['quality'], 10 );
		$vflip = isset( $values['vertical'] ) ? true : false;
		$hflip = isset( $values['horizontal'] ) ? true : false;
		$sharpness = intval( $values['sharpness'], 10 );
		$contrast = intval( $values['contrast'], 10 );
		$brightness = intval( $values['brightness'], 10 );
		$saturation = intval( $values['saturation'], 10 );
		$iso = intval( $values['iso'], 10 );

		$sharpness = min( 100, max( -100, $sharpness ) );
		$contrast = min( 100, max( -100, $contrast ) );
		$brightness = min( 100, max( 0, $brightness ) );
		$saturation = min( 100, max( -100, $saturation ) );

		$ifx = '';
		if ( isset( $this->get_effect_values()[ $values['effect'] ] ) ) {
			$ifx = $values['effect'];
		}

		$awb = 'auto';
		if ( isset( $this->get_awb_values()[ $values['awb'] ] ) ) {
			$awb = $values['awb'];
		}

		if ( !in_array( $iso, $this->get_iso_values() ) ) {
			$iso = 100;
		}

		$settings = array(
			'quality'    => $quality,
			'vflip'      => $vflip,
			'hflip'      => $hflip,
			'ifx'        => $ifx,
			'awb'        => $awb,
			'iso'        => $iso,
			'sharpness'  => $sharpness,
			'contrast'   => $contrast,
			'brightness' => $brightness,
			'saturation' => $saturation,
		);

		update_option( self::OPTION_NAME, $settings );
	}
}
