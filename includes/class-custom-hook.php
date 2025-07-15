<?php

namespace Integrazioni_Firma;


/*
* Hook custom per finalizzare il processo di firma bypassando anche contact form 7
*/

class Custom_Hook
{
   public static function init()
    {
        //TODO trovare hook sostitutivo
        add_action('XXXXwpcf7_before_send_mail', [__CLASS__, 'handle_form_custom']);
        add_action('wp_enqueue_scripts',      [__CLASS__, 'enqueue_js_custom']);
    }

    public static function handle_form_custom()
    {
        //Intercetto il click sul pulsante "download contract" e poi utilizzo i miei shortcode custom per iniettare i dati sul pdf
    }


    public static function enqueue_js_custom()
    {
        if (! is_page()) return;
        wp_enqueue_script('integrazioni-firma-custom', INTEGRAZIONI_FIRMA_URL . 'assets/js/custom-hook.js', ['jquery'], INTEGRAZIONI_FIRMA_VER, true);
       
    }
}

