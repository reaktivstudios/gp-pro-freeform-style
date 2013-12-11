//********************************************************
// load preview CSS
//********************************************************

function gppro_freeform_preview( freeformval ) {

	// set a variable for the style set
	var framehead	= jQuery( 'div.gppro-preview #gppro-preview-frame' ).contents().find( 'head' );

	// add the new one
	jQuery( framehead ).append( '<style class="gppro-preview-css" type="text/css">' + freeformval + '</style>');
}

//********************************************************
// now start the engine
//********************************************************

jQuery(document).ready( function($) {


// **************************************************************
//  load preview
// **************************************************************

	$( 'div.gppro-freeform-wrap' ).on( 'click', 'span.gppro-freeform-preview', function (event) {

		// get CSS values for preview reload
		var freeformval		= $( 'div.gppro-freeform-wrap' ).find( 'textarea' ).val();

		if ( freeformval === '' )
			return;

		// re process preview
		if ( $( 'div.gppro-frame-wrap' ).is( ':visible' ) )
			gppro_freeform_preview( freeformval );

	});


// ********************************************************
//  you're still here? it's over. go home.
// ********************************************************

});
