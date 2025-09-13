<?php

namespace Integrazioni_Firma;

use WP_Error;
use WPCF7_ContactForm;

class Cart_Handler
{

    public static function init() {}

    /**
     * Aggiunge un prodotto al carrello (guest o loggato) e persiste la sessione.
     *
     * @return int|\WP_Error  product_id se ok
     */
    public static function add_to_cart(int $product_id, int $qty = 1)
    {

      
        $ready = self::ensure_cart_is_ready();
        if (is_wp_error($ready)) {
            return $ready;
        }

        // 1) aggiungi al carrello
        $item_key = WC()->cart->add_to_cart($product_id, $qty);

        if (! $item_key) {
            Logger::error('Add to cart fallito. Prodotto: ' . $product_id);
            return new \WP_Error('add_failed', 'Impossibile aggiungere il prodotto al carrello.');
        }

        // 2) ricalcola totali (se ci sono altri prodotti nel carrelllo)
        //Uso le funzioni native di WooCommerce
        WC()->cart->calculate_totals();

        // 3) salva carrello + sessione
        WC()->cart->set_session();
        WC()->session->save_data();

        // 4) Configuro i cookie cart_hash & items_in_cart
        WC()->cart->maybe_set_cart_cookies();


        // 5) se guest: crea cookie wp_woocommerce_session_*
        /**
         *  N.B. per funzionare il negozio di woocommerce deve essere Live 
         *  woocommerce->impostazioni->visibilità del sito->Lve
         */
        if (!is_user_logged_in()) {
            WC()->session->set_customer_session_cookie(true);
        }

        return $product_id;
    }

    /**
     * Inizializza session, customer, cart se mancanti (AJAX/REST/Hook esterni).
     */
    private static function ensure_cart_is_ready()
    {

        if (! function_exists('WC')) {
            return new \WP_Error('no_wc', 'WooCommerce non è attivo.');
        }

        wc_maybe_define_constant('WOOCOMMERCE_CART', true);

        // SESSION
        if (null === WC()->session) {
            $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');
            WC()->session  = new $session_class();
            WC()->session->init();
        }

        // CUSTOMER
        if (null === WC()->customer) {
            WC()->customer = new \WC_Customer(get_current_user_id(), true);
        }

        // CART
        if (null === WC()->cart) {
            WC()->cart = new \WC_Cart();
            WC()->cart->get_cart(); // inizializza array
        }

        return true;
    }
}
