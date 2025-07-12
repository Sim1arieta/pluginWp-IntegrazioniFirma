<?php
/*
  Plugin Name: Integrazioni Firma
  Description: Plugin che implementa il processo di acquisto (WP-WC-Acf-eSign-FM)
  Version: 0.1.0
  Author: Simone Arieta
  Author URI: 
  Text Domain: sim1
  License: GPL -2.0+
*/

defined('ABSPATH') || exit;  //Protezione da accesso diretto

// ---------- Costanti di base ----------
define('INTEGRAZIONI_FIRMA_URL', plugin_dir_url(__FILE__));
define('INTEGRAZIONI_FIRMA_PATH', __DIR__);
define('INTEGRAZIONI_FIRMA_VER', '0.1.0');
// require_once __DIR__ . '/vendor/autoload.php'; // per il momento non mi serve



// Carico tutte le classi
foreach (glob(__DIR__ . '/includes/class-*.php') as $file) {
    require_once $file;
}


// ---------- Avvio ----------
add_action('plugins_loaded', function () {
  \Integrazioni_Firma\Assets::init();
  \Integrazioni_Firma\CF7_Hook::init();
  \Integrazioni_Firma\Cart_Handler::init();
  \Integrazioni_Firma\Reminder_Service::init();
  \Integrazioni_Firma\Shortcode_Manager::init();
});


// ---------- Cron ----------
register_activation_hook(__FILE__, function () {
  if (! wp_next_scheduled('integrazionifirma_reminder_cron')) {
    wp_schedule_event(time(), 'hourly', 'integrazionifirma_reminder_cron');
  }
});

register_deactivation_hook(__FILE__, function () {
  wp_clear_scheduled_hook('integrazionifirma_reminder_cron');
});
