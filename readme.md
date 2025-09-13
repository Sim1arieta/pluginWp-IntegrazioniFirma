# Integrazioni Firma – README

> Plugin WordPress che integra **Contact Form 7** → **ApproveMe WP E‑Signature** → **WooCommerce** per creare un prodotto “one‑shot” e aggiungerlo al carrello **dopo la firma** del contratto. Include reminder email e shortcode di servizio.

---

## Indice
- [Prerequisiti](#prerequisiti)
- [Installazione](#installazione)
- [Configurazione rapida](#configurazione-rapida)
- [Come funziona (overview)](#come-funziona-overview)
- [Flusso dettagliato (sequenza)](#flusso-dettagliato-sequenza)
- [Campi richiesti da CF7](#campi-richiesti-da-cf7)
- [Trigger di E‑Signature usati](#trigger-di-e-signature-usati)
- [Shortcode disponibili](#shortcode-disponibili)
- [Reminder pagamento](#reminder-pagamento)
- [Pulizia prodotti temporanei](#pulizia-prodotti-temporanei)
- [Hook/Filtri esposti dal plugin](#hookfiltri-esposti-dal-plugin)
- [Troubleshooting](#troubleshooting)
- [Struttura del plugin](#struttura-del-plugin)
- [Licenza](#licenza)

---

## Prerequisiti
- **WordPress** ≥ 6.5
- **PHP** ≥ 8.0
- **WooCommerce** ≥ 7.5 (attivo, modalità **sito live** in *WooCommerce → Impostazioni → Avanzate → Visibilità del sito*)
- **Contact Form 7** ≥ 5.7
- **WP E‑Signature by ApproveMe** (Business/Core + add‑on CF7)
- **ACF** (opzionale) – se vuoi leggere/scrivere campi ACF sui prodotti

> Nota: eventuali plugin di cache (es. LiteSpeed) devono **escludere** le pagine *Firma*, *Carrello* e *Checkout*.

---

## Installazione manuale
1. Carica la cartella del plugin in `wp-content/plugins/`.
2. Attiva **Integrazioni Firma** da **Bacheca → Plugin**.
3. All’attivazione il plugin verifica le dipendenze; se mancano, l’attivazione viene annullata con un messaggio.

---

## Configurazione rapida
1. **Contact Form 7**: crea/usa un form con i campi nascosti elencati in [Campi richiesti](#campi-richiesti-da-cf7) e integra la form con l’add‑on ApproveMe.
2. **ApproveMe**: configura il documento e associa la form CF7. Il submit della form aprirà il flusso di firma.
3. **WooCommerce**: controlla che le pagine **Carrello** e **Checkout** siano assegnate (Stato di WooCommerce verde) e che **Visibilità del sito** sia **Live**.
4. (Opz.) **ACF**: associa (o crea) un gruppo di campi per il post type `product` con le chiavi usate dal form.

---

## Come funziona (overview)
- L’utente clicca **Download Contract** (submit CF7) → ApproveMe genera il documento da firmare.
- **Dopo che tutte le firme sono completate**, il plugin intercetta l’hook di ApproveMe, recupera i **dati della form CF7** salvati dal loro add‑on, **crea un prodotto WooCommerce** con quei valori e lo **aggiunge al carrello** dell’utente.
- L’utente viene reindirizzato al **Carrello**; da lì può andare al **Checkout** e pagare subito o più tardi (con reminder automatici).

---

## Flusso dettagliato (sequenza)
1. **Submit CF7** → l’add‑on ApproveMe–CF7 salva i valori della form come JSON nel meta del documento (`esig_cf7_submission_value`).
2. **Firma completata** → ApproveMe esegue `do_action( 'esig_all_signature_request_signed', $payload )` dove `$payload` è un array con `document`, `recipient`, `invitation`.
3. **Hook del plugin** (`Esign_Hook::finalizza_firma`) legge il `$doc_id` dal payload, carica il JSON CF7 dal meta, lo `json_decode()` e passa i dati a `Product_Factory::create()`.
4. **Creazione prodotto** → prodotto virtuale, SKU univoco (`userId_timestamp`), prezzo/qty/tasse da form; eventuali campi **ACF** salvati se presenti.
5. **Add to cart** → `Cart_Handler::add_to_cart()` inizializza cart/sessione, aggiunge l’articolo, salva la sessione e imposta i cookie (anche per guest), poi reindirizza al **Carrello**.
6. **Reminder** → se l’ordine resta *pending*, il cron del plugin invia promemoria ogni 48h (max 3).

---

## Campi richiesti da CF7
Il form deve fornire almeno (nomi chiave case‑sensitive):
- `product_title` *(text)*
- `price` *(number/float come stringa)*
- `qta` *(int)*
- `acf_field_1` *(text – opzionale)*
- `acf_field_2` *(int – opzionale)*
- `acf_field_3` *(json – opzionale)*

**Esempio CF7 (semplificato)**
```text
[hidden product_title default:get]
[hidden price default:get]
[hidden qta default:get]
[hidden acf_field_1]
[hidden acf_field_2]
[hidden acf_field_3]
[submit "Download Contract"]
```
> I campi possono essere popolati via shortcode/JS/URL. L’add‑on ApproveMe–CF7 li serializza nel meta del documento.

---

## Trigger di E‑Signature usati
- **`esig_all_signature_request_signed`** *(action)* → scatta quando **tutti i firmatari** hanno concluso.
  - **Payload**: `array{ document, recipient, invitation }`
  - Dal `document` si ottiene `$doc_id` per leggere `esig_cf7_submission_value`.
- (Supportati internamente) `esig_document_created`, `esig_document_saved`, `esig_document_successfully_signed` – utili per estensioni/log.

---

## Shortcode disponibili
- `[if_product_title]` → titolo del prodotto presente nel carrello.
- `[if_product_price]` → prezzo (IVA inclusa) del prodotto nel carrello.
- `[if_acf_field key="acf_field_1"]` → valore di un campo ACF del prodotto nel carrello.
- `[if_product_meta key="copies_to_buy"]` → meta generico del prodotto nel carrello.

> Gli shortcode leggono dal **primo item** del carrello.

---

## Reminder pagamento
- **Hook cron**: `integrazionifirma_reminder_cron` pianificato **ogni ora** all’attivazione del plugin.
- **Logica**: invia un’email di promemoria (mittente `info@europebooks.com`) agli ordini `pending` più vecchi di 48h, massimo **3** volte per ordine (meta `_if_reminder_count`).
- **Template email**: usa la mail WooCommerce (testo semplice) con link a `$order->get_checkout_payment_url()`.

> Se usi plugin di queue/email esterni (es. SMTP), assicurati che WooCommerce possa inviare le mail di sistema.

---

## Pulizia prodotti temporanei
- I prodotti creati sono pensati come **usa‑e‑getta**. Configurazione proposta:
  - **Dopo 1 mese**: rendere il prodotto *catalogo nascosto* (non listato).
  - **Dopo 3 mesi**: cancellazione definitiva.

> La routine di cleanup può essere agganciata al cron del plugin (non abilitata di default nello scheletro).

---

## Hook/Filtri esposti dal plugin
Per personalizzazioni senza fork:
- **Action** `if_before_product_create( array $data_cf7 )` → prima che `Product_Factory` crei il prodotto.
- **Filter** `if_product_args` → filtra l’array di argomenti usato da `WC_Product_Simple`.
- **Action** `if_after_product_create( int $product_id, array $data_cf7 )`.
- **Action** `if_before_add_to_cart( int $product_id, int $qty )`.
- **Action** `if_after_add_to_cart( int $product_id, int $qty )`.

*(Se non presenti nella tua build, puoi aggiungerli facilmente attorno ai punti indicati.)*

---

## Troubleshooting
**Prodotto non appare nel carrello dopo il redirect**
- Verifica **WooCommerce → Visibilità del sito = Live**.
- Controlla nei DevTools del browser la presenza di **`wp_woocommerce_session_*`** oltre a `woocommerce_items_in_cart` e `woocommerce_cart_hash`.
- Escludi dalla cache le pagine *Firma/Carrello/Checkout* (vedi header `X-LiteSpeed-*`).
- Niente output prima dei redirect/cookie (nessun `echo`, `var_dump`, BOM).

**Non recupero i dati CF7**
- Leggi dal meta del documento **`esig_cf7_submission_value`** e decodifica con `json_decode( $json, true )`.
- In alternativa usa l’helper dell’add‑on CF7 `get_submission_value( $document_id, $form_id, $field_id )`.

**Guest non mantengono il carrello**
- Usa `Cart_Handler::add_to_cart()` dello scheletro che imposta sessione e cookie anche per guest.
- In locale **HTTP**, evita cookie `secure` oppure usa HTTPS locale (es. mkcert).

---

## Struttura del plugin
```
integrazioni-firma/
├─ integrazioni-firma.php               # bootstrap, dipendenze, cron
├─ includes/
│  ├─ class-assets.php                  # registrazione asset (SweetAlert2, css)
│  ├─ class-cf7-hook.php                # (se usato) hook submit CF7 lato front
│  ├─ class-esign-hook.php              # listener ApproveMe post-firma
│  ├─ class-product-factory.php         # creazione prodotto dinamico
│  ├─ class-product-meta.php            # helper meta/ACF
│  ├─ class-cart-handler.php            # add_to_cart sicuro (guest compresi)
│  ├─ class-reminder-service.php        # cron promemoria pagamento
│  └─ class-shortcode-manager.php       # shortcode di utilità
├─ assets/
│  ├─ js/cf7-hook.js                    # spinner/redirect CF7 (opzionale)
│  └─ css/frontend.css                  # stile opzionale
└─ README.md                            # questo file
```

---

## Licenza
- **Codice PHP**: `GPL-2.0-or-later` (compatibilità ecosistema WordPress).  
- **Asset JS/CSS/immagini**: puoi mantenerli proprietari per uso interno del cliente (vedi eventuale file `LICENSE-CLIENT.txt`).  
- Dipendenze terze: Woo/CF7/ApproveMe sono GPL; SweetAlert2 è MIT.



---

**Autore:** Simone Arieta  
**Versione:** 0.1.0  
**Text Domain:** `integrazioni-firma`

