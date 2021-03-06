<?php

namespace Revo;

use Bitrix\Conversion\DayContext;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;
use mysql_xdevapi\Exception;
use Revo\Models\RegisteredUsersTable;
use Revo\Helpers\Extensions;

class Events
{
    public function onProlog()
    {
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        global $USER;
        if (Loader::includeModule($moduleID)) {
            $a = \Bitrix\Main\Page\Asset::getInstance();
            $a->addJs('/bitrix/js/'.$moduleID.'/script.js');
            $a->addJs(Instalment::getInstance()->getEndpoint() . '/javascripts/iframe/v2/revoiframe.js');
            $a->addString('<link href="/bitrix/css/'.$moduleID.'/modal.css" type="text/css" rel="stylesheet" />');
            $a->addString('<script>REVO_PAY_SYSTEM_ID=' . intval(Option::get($moduleID, 'paysys_id', 0)) . ';</script>');
            $a->addString('<script>REVO_MIN_PRICE=' . intval(Option::get($moduleID, 'detail_min_price', 0)) . ';</script>');
			$a->addString('<script>REVO_MAX_PRICE=' . intval(Option::get($moduleID, 'detail_max_price', 0)) . ';</script>');
            $a->addString('<script>REVO_REQUEST_DECLINED=' . intval(RegisteredUsersTable::get(bitrix_sessid())['declined']) . ';</script>');
            $a->addString('<script>REVO_ADD_PRICE_BLOCK=1;</script>');
            $a->addString('<script>REVO_ORDERS_URL = "' .
                Option::get($moduleID, 'orders_url', '/personal/orders/') .
                '";</script>');
            \CJSCore::Init(array($moduleID));
        }
    }

    // вынесено в блок onOrderUpdate();
    // не удаляется во избежание ошибок во время отработки событий
    public function onStatusOrder($id, $val)
    {
        // ничего не делаем
    }

    public function onCancelOrder($id, $is_cancel, $description)
    {
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        IncludeModuleLangFile(__FILE__);
        $returnStatus = Option::get($moduleID, 'return_status', 'RB');
        $revoPaysysId = Option::get($moduleID, 'paysys_id', 0);
        $order = \CSaleOrder::GetById($id);

        if ($order['PAY_SYSTEM_ID'] == $revoPaysysId && $is_cancel == 'Y') {
            $revoClient = Instalment::getInstance();
            $result = $revoClient->returnOrder($id, $order['PRICE']);
            Logger::log($result, 'cancel');

            // если возврат или отмена прошли успешно - меняем статус на "Отменен" и отменяем оплату
            if ($result['status'] == 'ok') {
                \CSaleOrder::StatusOrder(
                    $order['ID'],
                    $returnStatus
                );

                // предотвращаем возврат средств на внутренний счет покупателя
                $payments = \Bitrix\Sale\Payment::getList([
                    'filter' => [
                        'ORDER_ID' => $order['ID'],
                        'PAY_SYSTEM_ID' => $revoPaysysId
                    ]
                ]);

                $sum = 0;
                while ($payment = $payments->fetch()) {
                    $sum += $payment['SUM'];
                }
                if ($order['PAYED'] == 'Y')
                    \CSaleUserAccount::UpdateAccount($order['USER_ID'], -$sum, $order['CURRENCY'], Loc::getMessage('REVO_CORRECT_BALANCE_MSG'), $order['ID']);

                // отмена оплаты
                \CSaleOrder::PayOrder(
                    $order['ID'],
                    'N'
                );
            }
        }
    }

    public function onUpdateOrder(\Bitrix\Main\Event $event)
    {
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        IncludeModuleLangFile(__FILE__);
        $returnStatus = Option::get($moduleID, 'return_status', 'RB');
        $finalStatus = Option::get($moduleID, 'finalization_status', 'F');
        $revoPaysysId = Option::get($moduleID, 'paysys_id', 0);
        $revoAdminEmail = Option::get($moduleID, 'email', '');


        $orderObj = $event->getParameter("ENTITY");
        $orderId = $orderObj->getId(); // ID заказа
        $status = $orderObj->getField('STATUS_ID'); // статус заказа

        $oldValues = $event->getParameter("VALUES");

        if ($orderObj->getField('PAY_SYSTEM_ID') == $revoPaysysId) {
            // если заказ не только что созданный
            if (!empty($orderId)) {
                // если поменялась стоимость заказа - отправить в Мокка
                if (!empty($oldValues['PRICE'])) {
                    $paymentCollection = $orderObj->getPaymentCollection();
                    foreach ($paymentCollection as $payment) {
                        $psID = $payment->getPaymentSystemId(); // ID платежной системы
                        if ($psID == $revoPaysysId) {
                            $revoClient = Instalment::getInstance();
                            $result = $revoClient->change($orderObj);
                            Logger::log($result, 'change');
                            if ($result['status'] != "ok") {
                                // блокировка сохранения заказа
                                return new \Bitrix\Main\EventResult(
                                    \Bitrix\Main\EventResult::ERROR,
                                    new \Bitrix\Sale\ResultError(Loc::getMessage('REVO_ERROR_TEXT_3').$result['msg']),
                                    'sale'
                                );
                            }
                            else {
                                $payment->setPaid("N"); // отменяем оплату
                                $payment->setField('SUM', $orderObj->getPrice());
                                $payment->setPaid("Y"); // выставляем оплату

                                // предотвращаем возврат средств на внутренний счет покупателя
                                $paidDiff = ($oldValues['PRICE'] - $orderObj->getPrice());
                                if ($paidDiff > 0) {
                                    \CSaleUserAccount::UpdateAccount($orderObj->getUserId(), -$paidDiff, $orderObj->getCurrency(), Loc::getMessage('REVO_CORRECT_BALANCE_MSG'), $orderId);
                                }
                            }
                        }
                    }
                }

                // если поменялся статус
                if (!empty($oldValues['STATUS_ID']) and $oldValues['STATUS_ID'] != $status) {
                    $order = \CSaleOrder::GetById($orderId);
                    if ($status == $finalStatus) {
                        $revoClient = Instalment::getInstance();

                        $pdfPath = '/upload/check/' . $order['ID'] . '.pdf';
                        $fullPdfPath = $_SERVER['DOCUMENT_ROOT'] . $pdfPath;
                        \Revo\Documents::billToPDF($order['ID'], $fullPdfPath);

                        $payments = \Bitrix\Sale\Payment::getList([
                            'filter' => [
                                'ORDER_ID' => $order['ID'],
                                'PAY_SYSTEM_ID' => $revoPaysysId
                            ]
                        ]);
                        $sum = 0;
                        while ($payment = $payments->fetch()) {
                            $sum += $payment['SUM'];
                        }
                        $result = $revoClient->finalizeOrder(
                            $order['ID'],
                            $sum ?: $order['PRICE'],
                            $fullPdfPath
                        );

                        if($result['result'] != 'error') {
                            Logger::log([
                                'Finalization have been sent to REVO', $result
                            ], 'finalization');
                        }

                        // если финализация не прошла
                        if ($result['result'] == 'error') {
                            if ($result['msg'] == 'Unable to finish - order is already finished/canceled') {
                                // блокировка сохранения заказа
                                return new \Bitrix\Main\EventResult(
                                    \Bitrix\Main\EventResult::ERROR,
                                    new \Bitrix\Sale\ResultError(Loc::getMessage('REVO_ERROR_TEXT_3').$result['msg']),
                                    'sale'
                                );
                            }

                            // пробуем еще раз финализировать, бывает не с первого раза срабатывает
                            $result = $revoClient->finalizeOrder(
                                $order['ID'],
                                $sum ?: $order['PRICE'],
                                $fullPdfPath
                            );

                            // если финализация снова не прошла
                            if ($result['result'] == 'error') {
                                $orderId = $order['ID'];
                                $sum = $sum ?: $order['PRICE'];

                                // создаем агента который с интервалом в 1 час 10 раз попробует отправить финализацию
                                $dateCurrent = date("d.m.Y H:i:s");
                                $dateFuture = date("d.m.Y H:i:s", strtotime($dateCurrent.'+ 1 minutes'));
                                \CAgent::AddAgent(
                                    "\Revo\Requests::statusOrder($orderId, $sum, 1);",
                                    $moduleID,
                                    "N",
                                    3600,
                                    "$dateFuture",
                                    'Y',
                                    "$dateFuture",
                                    '',
                                    '',
                                    'N'
                                );
                                // уведомляем админов партнера о том что финализация не прошла
                                if ($revoAdminEmail) {
                                    bxmail(
                                        $revoAdminEmail,
                                        Loc::getMessage('REVO_FINALIZATION_ERROR'),
                                        str_replace(
                                            ['#ERROR#', '#ORDER#'],
                                            [$result['msg'], $order['ID']],
                                            Loc::getMessage('REVO_ERROR_TEXT'))
                                    );
                                }
                                // аналогично уведомляем РЕВО
                                bxmail(
                                    "integration@revo.ru",
                                    Loc::getMessage('REVO_FINALIZATION_ERROR'),
                                    str_replace(
                                        ['#ERROR#', '#ORDER#', '#URL#'],
                                        [$result['msg'], $orderId, $_SERVER['HTTP_ORIGIN']],
                                        Loc::getMessage('REVO_ERROR_TEXT_2'))
                                );
                                Logger::log([
                                    "Заказ ID = " . $orderId . " не прошел финализацию в РЕВО.\n", $result
                                ], 'finalization-failed');
                                throw new \Bitrix\Sale\UserMessageException(Loc::getMessage('REVO_FINALIZATION_ERROR'));
                            }
                        }
                    }
                    elseif ($status == $returnStatus) {
                        // будет вызвано событие OnSaleCancelOrder
                        \CSaleOrder::CancelOrder($order['ID'], 'Y',
                            'Auto cancel from REVO when change status order to "return_status"');
                    }
                }
            }
            // если заказ новый
            elseif(empty($orderId)) {
                // ставим ему статус "Заказ оформлен, решение по займу не принято"
                $orderObj->setField('STATUS_ID', 'MN');
            }
        }
    }

    // теперь нет нужды отправлять /change при проставлении флага оплаты.
    // все происходит внутри onOrderUpdate();
    // не удаляется во избежание ошибок во время отработки событий
    public static function onSalePaymentPaid(Main\Event $event)
    {
        // ничего не делаем
    }

    public function appendJQuery() {
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        if (Option::get($moduleID, 'JQuery_selector', 'Y') == 'Y') {
            \CJSCore::init(array('jquery'));
        }
    }
}
