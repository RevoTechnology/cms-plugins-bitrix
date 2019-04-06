<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$action = trim($_REQUEST['action']);
\Bitrix\Main\Loader::includeModule('revo.instalment');

\Revo\Logger::log([$_REQUEST, $HTTP_RAW_POST_DATA]);

$result = false;
switch ($action) {
    case "registration_url":
        $el = \Revo\Instalment::getInstance();
        $result['url'] = $el->getRegistrationUri();
        break;
}
echo(json_encode($result));