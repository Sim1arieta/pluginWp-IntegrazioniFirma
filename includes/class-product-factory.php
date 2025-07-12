<?php

namespace Integrazioni_Firma;

class Product_Factory
{
  /**
   * Crea un prodotto virtuale su misura usando i dati del form CF7
   * Restituisce l'ID del prodotto creato.
   */
  public static function create(array $data): int
  {
    // Sanitize
    $title = sanitize_text_field($data['product_title'] ?? 'Prodotto Contratto');
    $price = floatval($data['price'] ?? 0);
    $qty   = intval($data['qta'] ?? 1);

    // SKU univoco: userID_timestamp
    $user  = get_current_user_id();
    $sku   = 'eb_' . $user . '_' . time();

    $product = new \WC_Product_Simple();
    $product->set_name($title);
    $product->set_regular_price($price);
    $product->set_sku($sku);
    $product->set_manage_stock(false);
    $product->set_stock_status('instock');
    $product->set_virtual(true);
    $product->set_tax_status('taxable'); // IVA standard
    $product->update_meta_data('copies_to_buy', $qty);

    // ACF placeholder metadata
    if (function_exists('update_field')) {
      update_field('acf_field_1', sanitize_text_field($data['acf_field_1'] ?? ''), $product->get_id());
      update_field('acf_field_2', intval($data['acf_field_2'] ?? 0),           $product->get_id());
      update_field('acf_field_3', wp_json_encode($data['acf_field_3'] ?? []),  $product->get_id());
    }

    return $product->save();
  }
}