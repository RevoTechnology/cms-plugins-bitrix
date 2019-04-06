<?php

namespace Revo;


class Logger
{
    public static function log($el, $prefix = 'log') {
        $logDir = $_SERVER['DOCUMENT_ROOT']. '/logs/';
        if (!file_exists($logDir)) mkdir($logDir);

        $fname = $prefix . '_' . date('Ymd');

        $fname = $logDir.$fname.'.log';

        $f = fopen($fname, 'a+');

        $el = print_r($el, true);

        fputs($f, '>>> '.
            date('Y-m-d H:i:s') . ' | ' .
            $_SERVER['REMOTE_ADDR'] . ' | ' .
            "\r\n");

        fputs($f, $el);
        fputs($f, "\r\n\r\n");

        fclose($el);
    }
}