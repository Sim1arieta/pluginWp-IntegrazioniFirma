<?php

namespace Integrazioni_Firma;

/**
 * Classe per gestione reminder
 */
class Reminder_Service
{
    const MAX_REMINDERS = 3;

    public static function init()
    {
        add_action('integrazionifirma_reminder_cron', [__CLASS__, 'process']);
    }

    public static function process()
    {
        $pending_orders = wc_get_orders([
            'status'        => 'pending',
            'date_modified' => '<' . (new \WC_DateTime('-48 hours'))->format('Y-m-d H:i:s'),
            'limit'         => -1,
        ]);

        foreach ($pending_orders as $order) {
            $count = intval($order->get_meta('_if_reminder_count', true));
            if ($count >= self::MAX_REMINDERS) {
                continue;
            }

            //Email
            //utilizzo le funzioni native di wp e wc
            wc_mail(
                $order->get_billing_email(),
                __('Promemoria: completa il pagamento del tuo contratto', 'integrazioni-firma'),
                sprintf(__('Puoi finalizzare l\'acquisto qui: %s', 'integrazioni-firma'), $order->get_checkout_payment_url()),
                ['From: info@europebooks.com']
            );

            $order->update_meta_data('_if_reminder_count', $count + 1);
            $order->save();
        }
    }
}
