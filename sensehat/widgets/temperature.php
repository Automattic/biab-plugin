<?php

class SenseHat_Temperature_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'sensehat_temperature_widget', // Base ID
			esc_html__( 'SenseHat Temperature', 'bloginbox' ), // Name
			array( 'description' => esc_html__( 'SenseHat Temperature Widget', 'bloginbox' ), ) // Args
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
		$temperature = count( $response->get_data() ) > 0 ? $response->get_data()[0]->temperature : null;
		echo '<div>';
		echo '<span>🌡</span>';
		echo esc_html( $this->get_temperature( $temperature ) );
		echo '</div>';
		echo $args['after_widget'];
	}

	private function get_temperature( $temp ) {
		$settings = new SensehatSettings();
		$units = $settings->get_units() === 'celsius' ? 'C' : 'F';

		if ( $temp ) {
			if ( $units === 'F' ) {
				$temp = $temp * ( 9 / 5 ) + 32;
			}

			return round( $temp, 1 ). ' °' . $units;
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
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Temperature', 'bloginbox' );
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
