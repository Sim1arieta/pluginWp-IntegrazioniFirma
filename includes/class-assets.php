<?php

namespace Integrazioni_Firma;

// Definizione della classe Assets per gestire gli asset in WordPress.
class Assets
{
    // Metodo statico per inizializzare l'enqueue degli script e stili.
    public static function init()
    {
        add_action('wp_enqueue_scripts',    [__CLASS__, 'register_front']); //richiama gli script per il frontend
        add_action('admin_enqueue_scripts', [__CLASS__, 'register_admin']); //richiama gli script per il backend(admin)
    }

    // Metodo statico per registrare gli asset del frontend.
    public static function register_front()
    {

        // main-if
        wp_register_script('main-if-js', INTEGRAZIONI_FIRMA_URL . 'assets/js/main-if.js', [], '2.4.1');
        wp_register_style( 'integrazioni-firma', INTEGRAZIONI_FIRMA_URL . 'assets/css/frontend.css');

        // sweetalert2
        wp_register_script('sim1-sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11',[],'2.4.1',true);
    

        //Carico solo i plugin dove servono
        if (is_page('pulsante-con-contact-form-7')) {//TODO renderlo dinamico
            
        }
            //js
            wp_enqueue_script('sim1-sweetalert2');
            
            //css
            wp_register_style('integrazioni-firma', INTEGRAZIONI_FIRMA_URL . 'assets/css/frontend.css');
    }



    // Metodo statico placeholder per eventuali asset lato admin.
    public static function register_admin()
    {
        // Placeholder per eventuali asset lato admin (forse)
    }
}
