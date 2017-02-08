<?php

class Sensehat_Database {
	private function get_table_name() {
		global $wpdb;

		return $wpdb->prefix . 'sense_hat';
	}

	public function create() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $this->get_table_name();

		$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				temperature double NOT NULL,
				humidity double NOT NULL,
				air_pressure double NOT NULL,
				created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public function remove() {
		global $wpdb;

		$wpdb->query( 'DROP TABLE '.$this->get_table_name() );
	}
}
