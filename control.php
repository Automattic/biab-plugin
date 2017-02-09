<?php

if ( !defined( 'ABSPATH' ) ) {
	die( 'Nope' );
}

if ( !function_exists( 'pr' ) ) {
	function pr( $things ) {
		echo '<pre>';
		print_r( $things );
		echo '</pre>';
	}
}

class BiabControl {
	const DEFAULT_PATH = '/opt/bloginabox';
	const OPTION_NAME = 'biab_path';
	const COMMAND = 'biab';

	public function get_path() {
		$path = get_option( self::OPTION_NAME );
		if ( !$path ) {
			$path = self::DEFAULT_PATH;
		}

		return $path;
	}

	public function set_path( $path ) {
		update_option( self::OPTION_NAME, rtrim( $path, '/' ) );
	}

	protected function request( $command, $data = '' ) {
		$file = 'sudo -u pi '.$this->get_path().'/'.self::COMMAND;

		$cmd = $file.' '.escapeshellarg( $command );
		$cmd .= ' '.escapeshellarg( $data );
		$cmd = escapeshellcmd( $cmd );

		$result = array();
		$retval = 0;

		exec( $cmd, $result, $retval );

		return array(
			'retval' => intval( $retval, 10 ),
			'output' => $result,
		);
	}
}
