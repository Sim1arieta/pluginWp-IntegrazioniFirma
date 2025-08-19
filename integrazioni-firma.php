<?php
/*
  Plugin Name: Integrazioni Firma
  Description: Plugin che implementa il pagamento del contratto sottoscritto contestualmente alla firma del medesimo
  Version: 0.9.0
  Author: Simone Arieta
  Author URI: 
  Text Domain: sim1
  License: GPL -2.0+

  Requires at least: 6.5
  Requires PHP: 8.0
  Requires Plugins: woocommerce, contact-form-7, e-signature, advanced-custom-fields
*/

defined('ABSPATH') || exit;  //Protezione da accesso diretto

// ---------- Costanti di base ----------
define('INTEGRAZIONI_FIRMA_URL', plugin_dir_url(__FILE__));
define('INTEGRAZIONI_FIRMA_PATH', __DIR__);
define('INTEGRAZIONI_FIRMA_VER', '0.1.0');
define('UPLOAD_DIR_PATH', wp_upload_dir());
define('URL_SITO', get_home_url());

add_action('woocommerce_init', function(){
  define('URL_CARRELLO', wc_get_cart_url()); //es. https://dominio/carrello/ 
  define('URL_CHECKOUT', wc_get_checkout_url()); //es. http://dominio/pagamento/ 
});


// Carico tutte le classi
foreach (glob(__DIR__ . '/includes/class-*.php') as $file) {
    require_once $file;
}


// ---------- Avvio ----------
add_action('plugins_loaded', function () {

  //Verifico prima le dipendenze
  $errors = [];

  if (! class_exists('WooCommerce')) $errors[] = 'WooCommerce';
  if (! class_exists('WPCF7_ContactForm')) $errors[] = 'Contact Form 7';
  if (! function_exists('WP_E_Sig')) $errors[] = 'WP E-Signature (ApproveMe)';

  // ACF non serve per il funzionamento base (per ora)
  if (!defined('IF_HAS_ACF')) {
    define('IF_HAS_ACF', function_exists('get_field'));
  }

  if ($errors) {
    //
    add_action('admin_notices', function () use ($errors) {
      echo '<div class="notice notice-error"><p><strong>Integrazioni Firma</strong>: dipendenze mancanti o non compatibili — ' .
        esc_html(implode(', ', $errors)) . '.</p></div>';
    });
    
    //Non inizializzo il plugin
    return;
  }

  \Integrazioni_Firma\Assets::init();
  \Integrazioni_Firma\CF7_Hook::init();
  \Integrazioni_Firma\Esign_Hook::init();
  \Integrazioni_Firma\Reminder_Service::init();
  \Integrazioni_Firma\Shortcode_Manager::init();
  \Integrazioni_Firma\Custom_Hook::init();
  \Integrazioni_Firma\Logger::init();
  if (class_exists('WooCommerce')) {
    Integrazioni_Firma\Cart_Handler::init();
  }
});


// ---------- Cron ----------
//TODO WORK IN PROGRESS
register_activation_hook(__FILE__, function () {
  if (! wp_next_scheduled('integrazionifirma_reminder_cron')) {
    wp_schedule_event(time(), 'hourly', 'integrazionifirma_reminder_cron');
  }
});

register_deactivation_hook(__FILE__, function () {
  wp_clear_scheduled_hook('integrazionifirma_reminder_cron');
});