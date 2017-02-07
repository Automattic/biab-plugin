<?php

class CameraControl {
	function take_photo() {
		$output = array();

		exec( "/opt/wp/photo.sh", $output );

		echo implode($output);
	}
}
