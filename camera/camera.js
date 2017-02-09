( function( $ ) {
	function takePhoto( url, data ) {
		$( '#TB_ajaxContent' ).html( $( '#camera-loading' ).html() );

		$.ajax( {
			type: 'POST',
			url: url,
			data: data,
			dataType: 'json',
			success: function( result ) {
				if ( result.error ) {
					$( '#TB_ajaxContent' ).html( $( '#camera-error' ).html() );
				} else {
					$( '#TB_ajaxContent' ).html( '<a href="' + result.url + '">' + result.image + '</a>' );
				}
			},
			error: function( result ) {
				$( '#TB_ajaxContent' ).html( $( '#camera-error' ).html() );
			}
		} );
	}

	$( document ).ready( function() {
		$( '#take-photo' ).click( function( ev ) {
			var form = $( this ).closest( 'form' );

			tb_show( ev.target.title, ev.target.href, false );
			takePhoto( form.attr( 'action' ), form.serialize() );
			ev.preventDefault();
		} );
	} );
} )( jQuery )
