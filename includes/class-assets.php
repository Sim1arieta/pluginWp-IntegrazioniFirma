<?php

namespace Integrazioni_Firma;

class Assets
{
    public static function init()
    {
        add_action('wp_enqueue_scripts',    [__CLASS__, 'register_front']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'register_admin']);
    }

    public static function register_front()
    {
        // SweetAlert2 (CDN) – caricato solo quando serve (CF7 form pages).
        wp_register_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', [], INTEGRAZIONI_FIRMA_VER, true);
        // CSS placeholder (crea /assets/css/frontend.css se ti serve).
        wp_register_style('integrazioni-firma', INTEGRAZIONI_FIRMA_URL . 'assets/css/frontend.css', [], INTEGRAZIONI_FIRMA_VER);
    }

    public static function register_admin()
    {
        // Placeholder per eventuali asset lato admin (forse)
    }
}
