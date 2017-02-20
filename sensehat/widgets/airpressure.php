<?php

class SenseHat_AirPressure_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'sensehat_airpressure_widget', // Base ID
			esc_html__( 'SenseHat Air Pressure', 'bloginbox' ), // Name
			array( 'description' => esc_html__( 'SenseHat Air Pressure Widget', 'bloginbox' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		$request = new WP_REST_Request( 'GET', '/biab/v1/sensehat' );
		$response = rest_do_request( $request );
		$air_pressure = count( $response->get_data() ) > 0 ? $response->get_data()[0]->air_pressure : null;
		echo '<div>';
		echo '<span>💨</span>';
		echo esc_html( $this->get_pressure( $air_pressure ) );
		echo '</div>';
		echo $args['after_widget'];
	}

	private function get_pressure( $pressure ) {
		if ( $pressure ) {
			return round( $pressure ).' mb';
		}

		return '-';
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Air Pressure', 'bloginbox' );
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'bloginbox' ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

}
