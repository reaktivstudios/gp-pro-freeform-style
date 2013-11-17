//********************************************************
// now start the engine
//********************************************************

jQuery(document).ready( function($) {

//********************************************************
// handle expandable textarea
//********************************************************

	$( 'div.gppro-slider-input' ).each(function() {

		$( this ).find( 'div.gppro-slide-toggle' ).slider();

	});

});
