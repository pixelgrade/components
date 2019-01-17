export class Woocommerce {

    public static bindEvents() {
        const $ = jQuery;
        $( 'body' ).on( 'adding_to_cart', () => {
            $( '.c-mini-cart' ).addClass( 'c-mini-cart--visible' );
        });

        $( '.c-mini-cart__overlay, .c-mini-cart__close').on( 'click', () => {
            $( '.c-mini-cart' ).removeClass( 'c-mini-cart--visible' );
        });
    }
}
