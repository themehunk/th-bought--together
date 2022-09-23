<?php 


function woocommerce_maybe_add_multiple_products_to_cart() {
// Make sure WC is installed, and add-to-cart qauery arg exists, and contains at least one comma.
if ( ! class_exists( 'WC_Form_Handler' ) || empty( $_REQUEST['add-to-cart'] ) || false === strpos( $_REQUEST['add-to-cart'], ',' ) ) {
    return;
}

// Remove WooCommerce's hook, as it's useless (doesn't handle multiple products).
remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );

$product_ids = explode( ',', $_REQUEST['add-to-cart'] );
$count       = count( $product_ids );
$number      = 0;

foreach ( $product_ids as $product_id ) {
    if ( ++$number === $count ) {
        // Ok, final item, let's send it back to woocommerce's add_to_cart_action method for handling.
        $_REQUEST['add-to-cart'] = $product_id;

        return WC_Form_Handler::add_to_cart_action();
    }

    $product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );
    $was_added_to_cart = false;
    $adding_to_cart    = wc_get_product( $product_id );

    if ( ! $adding_to_cart ) {
        continue;
    }

    $add_to_cart_handler = apply_filters( 'woocommerce_add_to_cart_handler', $adding_to_cart->product_type, $adding_to_cart );

    /*
     * Sorry.. if you want non-simple products, you're on your own.
     *
     * Related: WooCommerce has set the following methods as private:
     * WC_Form_Handler::add_to_cart_handler_variable(),
     * WC_Form_Handler::add_to_cart_handler_grouped(),
     * WC_Form_Handler::add_to_cart_handler_simple()
     *
     * Why you gotta be like that WooCommerce?
     */
    if ( 'simple' !== $add_to_cart_handler ) {
        continue;
    }

    // For now, quantity applies to all products.. This could be changed easily enough, but I didn't need this feature.
    $quantity          = empty( $_REQUEST['quantity'] ) ? 1 : wc_stock_amount( $_REQUEST['quantity'] );
    $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );

    if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity ) ) {
        wc_add_to_cart_message( array( $product_id => $quantity ), true );
    }
}
}

 // Fire before the WC_Form_Handler::add_to_cart_action callback.
 add_action( 'wp_loaded',        'woocommerce_maybe_add_multiple_products_to_cart', 15 );



  public function thwbt_add_all_to_cart() {

         if ( ! isset( $_POST['product_id'] ) ) {

            return;

        }

        check_ajax_referer( 'thwbt-addto-cart', 'thwbt_nonce' );

        $product_id     = (int) apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );

        $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, 1);

        $product        = wc_get_product( $product_id );

        $quantity       = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_POST['quantity'] ) );

        if($passed_validation && WC()->cart->add_to_cart($product_id, $quantity)){

          $data = apply_filters('add_to_cart_fragments', array());

          do_action( 'woocommerce_ajax_added_to_cart', $product_id );

          if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {

            wc_add_to_cart_message( array( $product_id => $quantity ), true );

            }

            WC_AJAX::get_refreshed_fragments();

        }else {
                        $data = array(
                            'error'       => true,
                            'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
                        );

                        wp_send_json( $data );
        }
        
        echo json_encode( $data );

        die();          

        }