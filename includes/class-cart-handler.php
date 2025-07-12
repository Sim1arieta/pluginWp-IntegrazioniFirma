<?php

namespace Integrazioni_Firma;

use WP_Error;
use WPCF7_ContactForm;

class Cart_Handler
{

    /**
     * Avvia gli hook del plugin.
     * Chiama questo metodo dal file principale del tuo plugin,
     * ad es. in `plugins_loaded` (priority > 10, così WooCommerce è già carico).
     */
    public static function init()
    {
        // integrazione con Contact Form 7
        // add_action('wpcf7_before_send_mail', [__CLASS__, 'handle_form'], 20, 1);
    }

    

    /**
     * Aggiunge un prodotto al carrello e restituisce l’URL del carrello.
     *
     * @param int $product_id
     * @param int $qty
     *
     * @return string|WP_Error
     */
    public static function add_to_cart(int $product_id, int $qty = 1)
    {

        /* Inizializza sessione + cart + customer */
        $ready = self::ensure_cart_is_ready();
        if (is_wp_error($ready)) {
            return $ready; // WooCommerce non attivo
        }

        /* Aggiunge il prodotto */
        $item_key = WC()->cart->add_to_cart($product_id, $qty);
       

        if (! $item_key) {
            return new WP_Error('add_failed', 'Impossibile aggiungere il prodotto al carrello.');
        }

        // Aggiorna i totali e crea il cookie solo per gli utenti loggati 
        // WC()->cart->calculate_totals();

        // if (is_user_logged_in()) {
        //     WC()->session->set_customer_session_cookie(true);
        // } else {
        //     // Guest: cookie di sessione *cart* (senza creare un customer)
        //     wc_setcookie('woocommerce_cart_hash', WC()->cart->get_cart_hash());
        //     wc_setcookie('woocommerce_items_in_cart', 1);
        // }

        return $product_id;
    }



    /**
     * Garantisce che cart, session e customer siano inizializzati
     * anche dentro richieste REST/AJAX.
     */
    private static function ensure_cart_is_ready()
    {

        // 1) WooCommerce installato?
        if (! function_exists('WC')) {
            return new \WP_Error('no_wc', 'WooCommerce non è attivo.');
        }

        // 2) Se *qualunque* pezzo manca, carichiamo l’intero stack.
        if ( WC()->session === null || WC()->cart === null) {

            /* Carica i file necessari se il core non l’ha già fatto */
            // if (! function_exists('wc_load_cart')) {
            //     include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
            // }
            // if (! class_exists('WC_Cart')) {
            //     include_once WC_ABSPATH . 'includes/class-wc-cart.php';
            // }
            // if (! class_exists('WC_Session_Handler')) {
            //     include_once WC_ABSPATH . 'includes/class-wc-session-handler.php';
            // }

            // Istanzia sessione, customer e cart 
            wc_load_cart(); //Se il cliente aggiunge anche altri lotti potrebbe dare problemi             

            // Per gli utenti anonimi impediamo il salvataggio a fine richiesta
            // if (!is_user_logged_in()) {
                // WC()->customer->set_is_persisted(false);
            // }
        }

        return true;
    }

}
