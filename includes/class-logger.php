<?php

namespace Integrazioni_Firma;

class Logger
{
    private static $log_file = INTEGRAZIONI_FIRMA_PATH . '\log\debug_if.log';
    private static $log_enabled = true;

    public static function init()
    {

        // Crea la directory se non esiste
        if (!file_exists(dirname(self::$log_file))) {
            wp_mkdir_p(dirname(self::$log_file));
        }
    }


    // Metodo getter
    public static function get_enabled($status)
    {
       return self::$log_enabled;
    }
    
    // Metodo setter
    public static function set_enabled($status)
    {
        self::$log_enabled = (bool)$status;
    }

    
    public static function log($message, $level = 'INFO')
    {
        if (!self::$log_enabled) return;
        if (!self::$log_file) return;

        $timestamp = current_time('mysql');
        $log_entry = "[{$timestamp}] [{$level}] {$message}\n";

        file_put_contents(self::$log_file, $log_entry, FILE_APPEND);
    }

    public static function error($message)
    {
        self::log($message, 'ERROR');
    }

    public static function warning($message)
    {
        self::log($message, 'WARNING');
    }

 
}

