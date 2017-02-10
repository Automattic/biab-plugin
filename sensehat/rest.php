<?php

class BiabSenseHAT_REST {

	public static function register_sensehat_routes( ) {
		register_rest_route( 'biab/v1', '/sensehat', array(
			'methods' => 'GET',
			'callback' => array( 'BiabSensehat_REST', 'get_reading' ),
		) );
		register_rest_route( 'biab/v1', '/sensehat', array(
			'methods' => 'POST',
			'callback' => array( 'BiabSensehat_REST', 'insert_reading' ),
		) );
	}

	public static function get_reading( WP_REST_Request $request ) {
		$default_values = (object) array(
			'temperature' => null,
			'humidity' => null,
			'air_pressure' => null,
			'timestamp' => null
		);

		global $wpdb;
		$values = $wpdb->get_row( "
			SELECT ROUND( temperature ) AS temperature,
				   ROUND( humidity ) AS humidity,
				   ROUND( air_pressure ) AS air_pressure,
				   created_at AS timestamp
			FROM wp_sense_hat
			ORDER BY id DESC LIMIT 1
			;
		" );

		return $values ? $values : $default_values;
	}

	public static function insert_reading( WP_REST_Request $request ) {
		global $wpdb;

		$rows = $wpdb->insert( 'wp_sense_hat',
			array(
				'temperature'  => $request->get_param( 'temperature' ),
				'humidity'     => $request->get_param( 'humidity' ),
				'air_pressure' => $request->get_param( 'air_pressure' ),
			),
			array(
				'%f',
				'%f',
				'%f',
			 )
		);

		return $request->get_params();
	}
}
