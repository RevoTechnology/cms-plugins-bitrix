<?php

#/local/modules/revo.instalment/tests
$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/../../../../');

function shutdown()
{
    $content = ob_get_contents();

    if (
        strpos($content, 'OK') === false ||
        strpos($content, 'Error') !== false ||
        strpos($content, 'error') !== false ||
        strpos($content, 'ERROR') !== false ||
        strpos($content, 'exception') !== false ||
        strpos($content, 'FAIL') !== false
    ) {
        exit(1);
    }
}

register_shutdown_function('shutdown');

$_SERVER['SERVER_NAME'] = array_key_exists('SERVER_NAME', $_SERVER)? $_SERVER['SERVER_NAME'] : $_GET['site'];

define('NO_KEEP_STATISTIC', true);
define('STOP_STATISTICS', true);
include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

error_reporting(E_ERROR);
ini_set('display_errors', '1');