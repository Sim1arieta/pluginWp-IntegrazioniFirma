<?php

namespace Integrazioni_Firma;

class CF7_Hook
{
    public static $datiProdotto;

    public static function init()
    {
        add_action('wpcf7_before_send_mail', [__CLASS__, 'handle_form']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueuecf7_js']);
    }

    public static function getDatiProdotto(){
        return self::$datiProdotto;
    }

    public static function setDatiProdotto($dati){
        self::$datiProdotto = $dati;
    }
    
    public static function handle_form(\WPCF7_ContactForm $form)
    {
        $submission = \WPCF7_Submission::get_instance();
        if (! $submission) {
            return;
        }

        $data = $submission->get_posted_data();
        self::setDatiProdotto($data);

        // Logger::log('setDatiProdotto');
        // Logger::log(serialize(self::$datiProdotto));
        //GENERAZIONE PRODOTTO E ADD TO CART SPOSTATI DOPO LA FIRMA DEL CONTRATTO
        // Crea prodotto
        // $product_id = Product_Factory::create($data);


        // Aggiunta al carrello
        // try {
        //     Cart_Handler::add_to_cart($product_id);
        //     // Logger::log($product_id);

        // } catch (\Throwable $th) {
        //     Logger::error($th->getMessage());
        // }
        
        // Salta l'email CF7 (Forse da integrare successivamente)
        add_filter('wpcf7_skip_mail', '__return_true');

        // Aggiungo la URL nel JSON di risposta di CF7
        add_filter('wpcf7_feedback_response', function ($response) { //hook funzionante ma deprecato: wpcf7_ajax_json_echo
            
            $response['checkout_url'] = URL_CHECKOUT;
            $response['cart_url'] = URL_CARRELLO;
            
            return $response;
        });
    }

    //Include il codice del plugin nella pagina
    public static function enqueuecf7_js()
    {
        // if (! is_page('PAGINA SPECIFICA')) return;
        //in fase di test lo carico sempre
        wp_enqueue_script('integrazioni-firma-cf7', INTEGRAZIONI_FIRMA_URL . 'assets/js/cf7-hook.js', ['jquery'], INTEGRAZIONI_FIRMA_VER, true);
       
    }
}


