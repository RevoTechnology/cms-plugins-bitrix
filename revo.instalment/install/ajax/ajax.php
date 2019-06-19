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
    case "register":
        $sessid = bitrix_sessid();
        $currentUser = \Revo\Models\RegisteredUsersTable::get($sessid);
        if (!$currentUser) {
            \Revo\Models\RegisteredUsersTable::addUser($sessid);
        } elseif ($currentUser['approved']) {
            $result['url'] = false;
            $result['message'] = 'registered';
            break;
        } elseif ($currentUser['declined']) {
            $result['message'] = 'declined';
            break;
        }

        $el = \Revo\Instalment::getInstance();
        $result['url'] = $el->getRegistrationUri($_SERVER['HTTP_REFERER']);
        break;
    default:
        $data = file_get_contents("php://input");
        $data = json_decode($data);
        /**
         * @var $data \Revo\Dto\OrderResponse
         */
        if ($data->order_id) {
            \Bitrix\Main\Loader::includeModule('sale');
            if (strpos($data->order_id, ':') !== false) {
                $session = array_shift(explode(':', $data->order_id));
                $registeredUser = \Revo\Models\RegisteredUsersTable::get($session);
                \Revo\Models\RegisteredUsersTable::update(
                    $registeredUser['id'],
                    [($data->decision == 'approved' ? 'approved' : 'declined') => true]
                );
                $result = ['result' => 'success', 'message' => 'User updated'];
            } else {
                $order = CSaleOrder::GetById($data->order_id);
                if ($order) {
                    $statusId = false;
                    $cancel = true;

                    switch ($data->decision) {
                        case "approved":
                            if (intval($data->amount) >= intval($arOrder['PRICE'])) {
                                CSaleOrder::PayOrder(
                                    $order['ID'],
                                    'Y'
                                );
                                $statusId = 'P';
                            } else {
                                CSaleOrder::Update(
                                    $order['ID'],
                                    ['SUM_PAID' => intval($data->amount)]
                                );
                            }
                            $cancel = false;

                            break;
                    }

                    if ($statusId) {
                        CSaleOrder::StatusOrder(
                            $order['ID'],
                            $statusId
                        );
                    } else if ($cancel) {
                        CSaleOrder::CancelOrder($order['ID'], 'Y', 'Auto cancel from revo service');
                    }
                    $result = ['result' => 'success', 'message' => 'Order updated'];
                }
            }
        }
        break;
}

echo(json_encode($result));