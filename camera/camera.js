( function( $ ) {
	function showCameraError() {
		$( '#camera-photo' ).hide();
		$( '#camera-error' ).show();
	}

	function takePhoto( url, data ) {
		$( '#camera-photo' ).show();
		$( '#camera-error' ).hide();

		$.ajax( {
			type: 'POST',
			url: url,
			data: data,
			dataType: 'json',
			success: function( result ) {
				$( '#camera-photo' ).hide();

				if ( result.error ) {
					showCameraError();
				} else {
					$( '#camera-result' ).html( '<a href="' + result.url + '">' + result.image + '</a>' );
				}
			},
			error: function( result ) {
				showCameraError();
			}
		} );
	}

	$( document ).ready( function() {
		$( '#take-photo' ).click( function( ev ) {
			var form = $( this ).closest( 'form' );

			takePhoto( form.attr( 'action' ), form.serialize() );
			ev.preventDefault();
		} );
	} );
} )( jQuery )
