<?php

namespace Revo;


use Bitrix\Main\Config\Option;
use Revo\Helpers\Extensions;

class Logger
{
    static $_debugMode = true;

    public static function log($el, $prefix = 'log')
    {
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        if (!self::$_debugMode) return;

        $bLog = Option::get($moduleID, 'log', 'Y') != 'N';

        if (!$bLog) return;

        $logDir = $_SERVER['DOCUMENT_ROOT']. '/logs/revo/';
        if (!is_dir($logDir)) mkdir($logDir, 755, true);

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

        fclose($f);
    }
}