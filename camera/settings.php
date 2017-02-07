<?php

class CameraSettings {
	const OPTION_NAME = 'biab_camera_settings';
	private $settings = array();

	public function __construct() {
		$this->settings = $this->get();
	}

	public function get_effects() {
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

	private function save_to_file( $values ) {
		$file = get_home_path().'photo.config';
		$flip = [];

		if ( $values['vflip'] ) {
			$flip[] = '-vf';
		}

		if ( $values['hflip'] ) {
			$flip[] = '-hf';
		}

		$config = array(
			'QUALITY='.$values['quality'],
			'FLIP="'.implode( ' ', $flip ).'"',
			'OTHER="--imxfx '.$values['ifx'].'"',
			'',
		);

		file_put_contents( $file, implode( "\n", $config ) );
	}

	public function get() {
		$defaults = array(
			'quality' => 75,
			'ifx'     => '',
			'vflip'      => false,
			'hflip'      => false,
			'sharpness'  => '',
			'contrast'   => '',
			'brightness' => '',
			'saturation' => '',
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

		$sharpness = min( 100, max( -100, $sharpness ) );
		$contrast = min( 100, max( -100, $contrast ) );
		$brightness = min( 100, max( 0, $brightness ) );
		$saturation = min( 100, max( -100, $saturation ) );

		$ifx = '';
		if ( isset( $this->get_effects()[ $values['effect'] ] ) ) {
			$ifx = $values['effect'];
		}

		$settings = array(
			'quality'    => $quality,
			'vflip'      => $vflip,
			'hflip'      => $hflip,
			'ifx'        => $ifx,
			'sharpness'  => $sharpness,
			'contrast'   => $contrast,
			'brightness' => $brightness,
			'saturation' => $saturation,
		);

		update_option( self::OPTION_NAME, $settings );

		$this->save_to_file( $settings );
	}
}
