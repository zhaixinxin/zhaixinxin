(function($) {
    console.log("loading test.js");
  wp.customize( login_cust.name, function( value ) {
      console.log("in customize with variable name " + login_cust.name + " value = "  + value);
    value.bind( function( to ) {
        console.log("in bind with value " + to);
		$( 'html, body' ).css('background-color', to);
    } );
  } );
})( jQuery, login_cust );