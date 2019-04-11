<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$action = trim($_REQUEST['action']);
\Bitrix\Main\Loader::includeModule('revo.instalment');


\Revo\Logger::log([
    $_REQUEST,
    file_get_contents("php://input")
]);

$result = false;
switch ($action) {
    case "registration_url":
        $el = \Revo\Instalment::getInstance();
        $result['url'] = $el->getRegistrationUri();
        break;
    default:
        $data = file_get_contents("php://input");
        $data = json_decode($data);
        /**
         * @var $data \Revo\Dto\OrderResponse
         */
        \Revo\Logger::log($data, 'order');
        if ($data->order_id) {
            \Bitrix\Main\Loader::includeModule('sale');
            $order = CSaleOrder::GetById($data->order_id);
            \Revo\Logger::log($order, 'order');
            if ($order) {
                $statusId = false;
                switch ($data->decision) {
                    case "approved":
                        $statusId = 'P';
                        CSaleOrder::PayOrder(
                            $order['ID'],
                            'Y'
                        );
                        break;
                }

                if ($statusId) {
                    CSaleOrder::StatusOrder(
                        $order['ID'],
                        $statusId
                    );
                } else {
                    CSaleOrder::CancelOrder($order['ID'], 'Y', 'Auto cancel from revo service');
                }
            }
        }
        break;
}
echo(json_encode($result));