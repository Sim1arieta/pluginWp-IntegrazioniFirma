<?php

namespace Integrazioni_Firma;

class Product_Meta
{

    public static function get_acf(int $product_id, string $key)
    {
        return function_exists('get_field') ? get_field($key, $product_id) : null;
    }

    public static function get_meta(int $product_id, string $meta_key)
    {
        return get_post_meta($product_id, $meta_key, true);
    }
}
