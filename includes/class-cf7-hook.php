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

        // Salva product_id se serve in futuro
        // $submission->add_uploaded_file('if_product_id', $product_id);

        // Aggiunta al carrello + redirect
        Cart_Handler::add_to_cart_and_redirect($product_id);

        // Salta l’email CF7
        add_filter('wpcf7_skip_mail', '__return_true');
    }

    public static function enqueue_js()
    {
        if (! is_page()) return;
        wp_enqueue_script('integrazioni-firma-cf7', INTEGRAZIONI_FIRMA_URL . 'assets/js/cf7-hook.js', ['jquery'], INTEGRAZIONI_FIRMA_VER, true);
        wp_localize_script('integrazioni-firma-cf7', 'ifData', [
            'cartUrl' => wc_get_cart_url(),
        ]);
    }
}


