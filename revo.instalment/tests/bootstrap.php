<?php

#/local/modules/a.revo/tests
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
define('NOT_CHECK_PERMISSIONS', true);
define('SITE_ID', 's1');
define('LANGUAGE_ID', 'ru');
define('LID', 'ru');

include_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

error_reporting(E_ERROR);
ini_set('display_errors', '1');

function postRequest($url, $data, $json = false) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json ? json_encode($data) : http_build_query($data));
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_URL, 'https://' . $_SERVER['HTTP_HOST'] . $url);
    curl_setopt($ch, CURLOPT_REFERER, 'https://' . $_SERVER['HTTP_HOST'] . $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $webPage = curl_exec($ch);
    curl_close($ch);
    return $webPage;
}
