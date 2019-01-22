(function($) {

	// when document is ready
	$(function() {

		// show mini cart when a product is added to cart
		$( 'body' ).not( '.woocommerce-cart' ).on( 'adding_to_cart', function() {
			$( '.c-mini-cart' ).addClass( 'c-mini-cart--visible' );
		});

		// hide mini cart when user clicks outside the mini cart or on the close button
		$( '.c-mini-cart__overlay, .c-mini-cart__close').on( 'click', function() {
			$( '.c-mini-cart' ).removeClass( 'c-mini-cart--visible' );
		});

	});

})(jQuery);