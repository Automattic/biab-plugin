<?php

class BiabSenseHAT_REST {

	const API_NAMESPACE = 'biab/v1';
	const API_ROUTE = '/sensehat';

	public static function register_sensehat_routes( ) {
		register_rest_route( self::API_NAMESPACE, self::API_ROUTE, array(
			'methods' => 'GET',
			'callback' => array( 'BiabSenseHAT_REST', 'get_reading' ),
			'args' => BiabSenseHAT_REST::check_args(),
		) );
		register_rest_route( self::API_NAMESPACE, self::API_ROUTE, array(
			'methods' => 'POST',
			'callback' => array( 'BiabSenseHAT_REST', 'insert_reading' ),
			'permission_callback' => function() {
				return current_user_can( 'edit_others_posts' );
			},
		) );
	}

	public static function check_args() {
		$args = array();

		$args['temperature'] = array(
			'description'       => esc_html__( 'The units for the temperature. Valid values are celsius or fahrenheit.', 'bloginbox' ),
			'type'              => 'string',
			'validate_callback' => array( 'BiabSenseHAT_REST', 'validate_temp' ),
			'sanitize_callback' => array( 'BiabSenseHAT_REST', 'sanitize_arg' ),
			'required'          => false,
		);

		$args['before'] = array(
			'description'       => esc_html__( 'The ending point of the temperature range. A string such as 2016-01-23', 'bloginbox' ),
			'type'              => 'string',
			'validate_callback' => array( 'BiabSenseHAT_REST', 'validate_range' ),
			'sanitize_callback' => array( 'BiabSenseHAT_REST', 'sanitize_arg' ),
			'required'          => false,
		);

		$args['after'] = array(
			'description'       => esc_html__( 'The starting point of the temperature range. A string such as 2016-01-23', 'bloginbox' ),
			'type'              => 'string',
			'validate_callback' => array( 'BiabSenseHAT_REST', 'validate_range' ),
			'sanitize_callback' => array( 'BiabSenseHAT_REST', 'sanitize_arg' ),
			'required'          => false,
		);

		return $args;
	}

	public static function is_valid_date( $date ) {
		$parts = explode( '-', $date );
		if( (count( $parts) === 3 )
			&& is_numeric( $parts[ 0 ] ) && is_numeric( $parts[ 1 ] ) && is_numeric( $parts[ 2 ] )
			&& checkdate( $parts[ 1 ], $parts[ 2 ], $parts[ 0 ] ) ) {
			return true;
		}
		return false;
	}

	public static function validate_range( $value, $request, $param ) {
		$attributes = $request->get_attributes();

		if ( isset( $attributes['args'][ $param ] ) ) {
			$argument = $attributes['args'][ $param ];
			if ( ! is_string( $value ) || ! BiabSenseHAT_REST::is_valid_date( $value ) ) {
				return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%1$s either is not a date or has not a valid format such as yyyy-mm-dd', 'bloginbox' ), $param ), array( 'status' => 400 ) );
			}
		}
		return true;
	}


	public static function validate_temp( $value, $request, $param ) {
		$attributes = $request->get_attributes();

		if ( isset( $attributes['args'][ $param ] ) ) {
			$argument = $attributes['args'][ $param ];
			if ( ! is_string( $value ) || ! in_array( $value, array( 'celsius', 'fahrenheit' ) ) ) {
				return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%1$s either is not a string or is not a valid value (celsius or fahrenheit)', 'bloginbox' ), $param ), array( 'status' => 400 ) );
			}
		}
		return true;
	}

	public static function sanitize_arg( $value, $request, $param ) {
		return sanitize_text_field( $value );
	}

	public static function get_reading( WP_REST_Request $request ) {
		$temperature = 'temperature';
		if( isset( $request[ 'temperature' ]) && ($request[ 'temperature' ] == 'fahrenheit' ) ) {
			$temperature = 'temperature * (9/5) + 32';
		}
		if( isset( $request[ 'before' ] ) && isset( $request[ 'after' ] ) ) {
			$sql_query = "
			SELECT ".$temperature." AS temperature,
				humidity AS humidity,
				air_pressure AS air_pressure,
				created_at AS timestamp
			FROM wp_sense_hat
			WHERE date(created_at) >= '".$request['after']."'
				AND date(created_at) <= '".$request['before']."'
			";
		} else {
			$sql_query = "
			SELECT ".$temperature." AS temperature,
				humidity AS humidity,
				air_pressure AS air_pressure,
				created_at AS timestamp
			FROM wp_sense_hat
			ORDER BY id DESC LIMIT 1;
			";
		}

		global $wpdb;
		$values = $wpdb->get_results( $sql_query );
		return $values ? $values : [];

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
