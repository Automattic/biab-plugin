( function( $ ) {
	function publishReport( url, data ) {
		$.ajax( {
			type: 'POST',
			url: url,
			data: data,
			dataType: 'json',
			success: function( result ) {
				$( '#publish-report' ).removeClass( "updating-message" );
				if ( result.error ) {
					console.error('err on success');
				} else {
					$( '#publish-report' ).parent().append('<p id="report-published">Report has been <a href="' + result.url + '" target="_blank">published</a></p>');
					console.log( result.post_id + ' ' + result.url );
				}
			},
			error: function( result ) {
				$( '#publish-report' ).removeClass( "updating-message" );
				console.error('err on err');
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
