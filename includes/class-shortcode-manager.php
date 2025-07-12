<?php

namespace Integrazioni_Firma;

class Shortcode_Manager
{
    public static function init()
    {
        add_shortcode('if_product_title', [__CLASS__, 'product_title_cb']);
        add_shortcode('if_product_price', [__CLASS__, 'product_price_cb']);
        add_shortcode('if_acf_field',    [__CLASS__, 'acf_field_cb']);
        add_shortcode('if_product_meta', [__CLASS__, 'meta_cb']);
    }

    private static function current_product_id(): int
    {
        $cart = WC()->cart->get_cart();
        return $cart ? array_key_first($cart) : 0;
    }

    public static function product_title_cb()
    {
        $id = self::current_product_id();
        return $id ? get_the_title($id) : '';
    }

    public static function product_price_cb()
    {
        $id = self::current_product_id();
        return $id ? wc_price(wc_get_price_including_tax(wc_get_product($id))) : '';
    }

    public static function acf_field_cb($atts)
    {
        $atts = shortcode_atts(['key' => ''], $atts);
        $id = self::current_product_id();
        return $id ? esc_html(Product_Meta::get_acf($id, $atts['key'])) : '';
    }

    public static function meta_cb($atts)
    {
        $atts = shortcode_atts(['key' => ''], $atts);
        $id = self::current_product_id();
        return $id ? esc_html(Product_Meta::get_meta($id, $atts['key'])) : '';
    }
}
