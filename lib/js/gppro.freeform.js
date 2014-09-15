//********************************************************************************************************************************
// load global preview CSS
//********************************************************************************************************************************
function gppro_freeform_preview( freeformval, viewport ) {
	// set a variable for the style set
	var framehead	= jQuery( '#gppro-preview-frame' ).contents().find( 'head' );
	// add the mobile only viewport
	if ( viewport == 'mobile' ) {
		jQuery( framehead ).append( '<style class="gppro-preview-css" type="text/css">@media only screen and (max-width: 480px) {' + freeformval + ' }</style>');
	}
	// add the tablet only viewport
	if ( viewport == 'tablet' ) {
		jQuery( framehead ).append( '<style class="gppro-preview-css" type="text/css">@media only screen and (max-width: 768px) {' + freeformval + ' }</style>');
	}
	// add the desktop only viewport
	if ( viewport == 'desktop' ) {
		jQuery( framehead ).append( '<style class="gppro-preview-css" type="text/css">@media only screen and (min-width: 1024px) {' + freeformval + ' }</style>');
	}
	// add the global viewport
	if ( viewport == 'global' ) {
		jQuery( framehead ).append( '<style class="gppro-preview-css" type="text/css">' + freeformval + '</style>');
	}
}

//********************************************************************************************************************************
// now start the engine
//********************************************************************************************************************************
jQuery(document).ready( function($) {

//********************************************************************************************************************************
// load global preview
//********************************************************************************************************************************
	$( '.gppro-freeform-wrap' ).on( 'click', '.gppro-freeform-preview', function () {
		// get parent wrap
		var textwrap = $( this ).parents( '.gppro-freeform-wrap' );
		// get viewport
		var viewport = $( this ).data( 'viewport' );
		// set viewport to global if not present
		if ( viewport === '' ) {
			viewport = 'global';
		}
		// get CSS values for preview reload
		var freeformval = $( textwrap ).find( 'textarea' ).val();
		// bail if no values being passed
		if ( freeformval === '' ) {
			return;
		}
		// re process preview
		if ( $( '.gppro-frame-wrap' ).is( ':visible' ) ) {
			gppro_freeform_preview( freeformval, viewport );
		}
	});

//********************************************************************************************************************************
//  you're still here? it's over. go home.
//********************************************************************************************************************************
});
