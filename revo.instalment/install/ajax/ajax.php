<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$action = trim($_REQUEST['action']);

$result = false;
switch ($action) {
    case "registration_url":
        \Bitrix\Main\Loader::includeModule('revo.instalment');
        $el = \Revo\Instalment::getInstance();
        $result['url'] = $el->getIframeUri();
        break;
}
echo(json_encode($result));