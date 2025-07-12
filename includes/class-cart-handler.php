<?php

namespace Integrazioni_Firma;

class Cart_Handler
{
    public static function init() {}

    public static function add_to_cart_and_redirect(int $product_id)
    {
        WC()->cart->add_to_cart($product_id);
        wp_safe_redirect(wc_get_cart_url());
        exit;
    }
}
