( function( $ ) {
	function publishReport( url, data ) {
		$.ajax( {
			type: 'POST',
			url: url,
			data: data,
			dataType: 'json',
			success: function( result ) {
				var button = $( '#publish-report' );
				button.removeClass( "updating-message" );
				if ( result.error ) {
					button.parent().append('<p id="report-published">Report could not be published: ' + result.error );
				} else {
					button.parent().append('<p id="report-published">Report has been <a href="' + result.url + '" target="_blank">published</a></p>');
				}
			},
			error: function( result ) {
				var button = $( '#publish-report' );
				button.removeClass( "updating-message" );
				button.parent().append('<p id="report-published">Report could not be published: ' + result );
			}
		} );
	}

	$( document ).ready( function() {
		$( '#publish-report' ).click( function( ev ) {
			var form = $( this ).closest( 'form' );
			$( this ).addClass( "updating-message" );
			$( '#report-published' ).remove();
			publishReport( form.attr( 'action' ), form.serialize() );
			ev.preventDefault();
		} );
	} );
} )( jQuery )
