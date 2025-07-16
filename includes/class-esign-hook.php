<?php

namespace Integrazioni_Firma;
/*
    | Hook e-signature                        | Tipo   | Quando scatta                                                                            | Parametri principali                             |
    | --------------------------------------- | ------ | ---------------------------------------------------------------------------------------- | ------------------------------------------------ |
    | **`esig_document_created`**             | action | appena un documento viene creato (bozza o ready-to-sign)                                 | `( $doc_id, $args )`                             |
    | **`esig_document_saved`**               | action | ogni salvataggio/aggiornamento di un documento                                           | `( $doc_id, $user_id )`                          |
    | **`esig_document_status_updated`**      | action | ogni cambio di stato (`draft → awaiting`, `awaiting → signed`, ecc.)                     | `( $doc_id, $old_status, $new_status )`          |
  =>| **`esig_document_successfully_signed`** | action | quando **tutti** i firmatari hanno completato la firma                                   | `( $doc_id )`                                    |
  =>| **`esig_signature_saved`**              | action | ogni volta che un singolo firmatario completa la propria firma                           | `( $doc_id, $signer_id, $signature_id )`         |
    | **`esig_invite_user_sent`**             | action | quando parte un’email d’invito alla firma                                                | `( $doc_id, $recipient_email )`                  |
    | **`esig_reminder_email_sent`**          | action | quando parte un promemoria automatico                                                    | `( $doc_id, $recipient_email )`                  |
    | **`esig_pdf_before_output`**            | filter | consente di aggiungere/modificare contenuto prima che il PDF venga spedito o mostrato    | `( $tcpdf_instance, $doc_id ) → TCPDF`           |
    | **`esig_document_placeholder_value`**   | filter | ti permette di sostituire/integrare variabili tipo `{{custom_field}}` dentro al template | `( $value, $placeholder_key, $doc_id ) → string` |
    | **`esig_allowed_shortcodes`**           | filter | per registrare shortcode custom che l’editor di WP E-Signature può usare nei contratti   | `( $array_di_shortcode ) → array`                |
         esig_all_signature_request_signed
    =>* Hook papabili

    "esig_signature_saved" esiste
    esig_all_signature_request_signed esiste
*/


class Esign_Hook
{
    private static $table_prefix;
    private static $table_prefix_esig;
    private static $table;
    private static $meta_key = 'esig_cf7_submission_value';

    public static function init()
    {
    
        global $wpdb;

        self::$table_prefix = $wpdb->prefix;
        self::$table_prefix_esig =  self::$table_prefix . "esign_";   
        self::$table = self::$table_prefix_esig . "documents_meta";

        add_action('esig_all_signature_request_signed', [__CLASS__, 'finalizza_firma']);
    }

    public static function finalizza_firma($payload)
    {

        $doc = $payload['document'];
        $recipient  = $payload['recipient'];
        $invitation = $payload['invitation'];

        $doc_id = $doc->document_id;
        // Logger::log('Doc_id: '. $doc_id);

        $dati_form_json = self::get_cf7_submission_value($doc_id, self::$meta_key);

        $dati_form = json_decode($dati_form_json, true);
        
    
        // print_r($dati_form);

        if (!empty($dati_form)) {
            try {
                $product_id = Product_Factory::create($dati_form);
                // Logger::log('product_id: '.$product_id);

            } catch (\Throwable $th) {
                Logger::error($th->getMessage());
            }
            
            Cart_Handler::add_to_cart($product_id);
        }

         self::redirect_to_cart();
    }


    public static function get_cf7_submission_value($document_id, $meta_key)
    {
        
        global $wpdb;

        $meta = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT meta_value FROM " . self::$table . " WHERE document_id=%d and meta_key=%s LIMIT 1",
                $document_id,
                $meta_key
            )
        );

        if (isset($meta)){
            return $meta->meta_value;
        }else{
            return false;
        } 
    }

    public static function redirect_to_cart()
    {
        try {
            wp_redirect(URL_CARRELLO);
            exit;
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
        }
    }
}