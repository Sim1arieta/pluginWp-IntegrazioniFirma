<?php

namespace Integrazioni_Firma;

class CF7_Hook
{
    public static function init()
    {
        add_action('wpcf7_before_send_mail', [__CLASS__, 'handle_form']);
        add_action('wp_enqueue_scripts',      [__CLASS__, 'enqueue_js']);
    }

    public static function handle_form(\WPCF7_ContactForm $form)
    {
        $submission = \WPCF7_Submission::get_instance();
        if (! $submission) {
            return;
        }
        $data = $submission->get_posted_data();

        // Crea prodotto
        $product_id = Product_Factory::create($data);


        // Aggiunta al carrello
        Cart_Handler::add_to_cart($product_id);
        
        // Salta l'email CF7
        add_filter('wpcf7_skip_mail', '__return_true');

        //wp_redirect(URL_CARRELLO);

        // Iniettiamo la URL nel JSON di risposta di CF7
        add_filter('wpcf7_feedback_response', function ($response) { //hook funzionante ma deprecato: wpcf7_ajax_json_echo
            $response['checkout_url'] = URL_CHECKOUT;
            $response['cart_url'] = URL_CARRELLO;
            return $response;
        });
    }

    public static function enqueue_js()
    {
        if (! is_page()) return;
        wp_enqueue_script('integrazioni-firma-cf7', INTEGRAZIONI_FIRMA_URL . 'assets/js/cf7-hook.js', ['jquery'], INTEGRAZIONI_FIRMA_VER, true);
       
    }
}


