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

    public static function init()
    {
        add_action('esig_all_signature_request_signed', [__CLASS__, 'redirect_to_cart']);
    }

    public static function redirect_to_cart()
    {
        error_log('URL_CARRELLO');
        wp_redirect(URL_CARRELLO);
    }
}