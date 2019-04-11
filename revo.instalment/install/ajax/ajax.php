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
        $sessid = bitrix_sessid();
        $currentUser = \Revo\Models\RegisteredUsersTable::get($sessid);
        if (!$currentUser) {
            \Revo\Models\RegisteredUsersTable::addUser($sessid);
        } elseif ($currentUser['approved']) {
            $result['url'] = false;
            $result['message'] = 'registered';
            break;
        }

        $el = \Revo\Instalment::getInstance();
        $result['url'] = $el->getRegistrationUri();
        break;
    default:
        $data = file_get_contents("php://input");
        $data = json_decode($data);
        /**
         * @var $data \Revo\Dto\OrderResponse
         */
        if ($data->order_id) {
            \Bitrix\Main\Loader::includeModule('sale');
            if ($registeredUser = \Revo\Models\RegisteredUsersTable::get($data->order_id)) {
                \Revo\Models\RegisteredUsersTable::update(
                    $registeredUser['id'],
                    ['approved' => true]
                );
                $result = ['result' => 'success', 'message' => 'User updated'];
            } else {
                $order = CSaleOrder::GetById($data->order_id);
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
                    $result = ['result' => 'success', 'message' => 'Order updated'];
                }
            }
        }
        break;
}

echo(json_encode($result));