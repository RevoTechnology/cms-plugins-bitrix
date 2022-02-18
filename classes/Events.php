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

    public function onStatusOrder($id, $val)
    {
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        IncludeModuleLangFile(__FILE__);
        $returnStatus = Option::get($moduleID, 'return_status', 'RP');
        $finalStatus = Option::get($moduleID, 'finalization_status', 'F');
        $revoPaysysId = Option::get($moduleID, 'paysys_id', 0);
        $revoAdminEmail = Option::get($moduleID, 'email', '');
        $order = \CSaleOrder::GetById($id);

        if ($order['PAY_SYSTEM_ID'] == $revoPaysysId) {
            if ($val == $finalStatus) {
                $revoClient = Instalment::getInstance();

                $pdfPath = '/upload/check/' . $id . '.pdf';
                $fullPdfPath = $_SERVER['DOCUMENT_ROOT'] . $pdfPath;
                \Revo\Documents::billToPDF($id, $fullPdfPath);

                $payments = \Bitrix\Sale\Payment::getList([
                    'filter' => [
                        'ORDER_ID' => $id,
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
            } elseif ($val == $returnStatus) {
                $revoClient = Instalment::getInstance();
                $result = $revoClient->returnOrder($id, $order['PRICE']);
                Logger::log($result, 'cancel');
            }
        }
    }

    public function onCancelOrder($id, $is_cancel, $description)
    {
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        IncludeModuleLangFile(__FILE__);
        $revoPaysysId = Option::get($moduleID, 'paysys_id', 0);
        $order = \CSaleOrder::GetById($id);

        if ($order['PAY_SYSTEM_ID'] == $revoPaysysId && $is_cancel == 'Y') {
            $revoClient = Instalment::getInstance();
            // @FIXME change to order payments with $revoPaysysId instead order
            $result = $revoClient->returnOrder($id, $order['PRICE']);
            Logger::log($result, 'cancel');
        }
    }

    public function onUpdateOrder($id, $arFields)
    {
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        IncludeModuleLangFile(__FILE__);
        $revoPaysysId = Option::get($moduleID, 'paysys_id', 0);
        $order = \CSaleOrder::GetById($id);

        if ($order['PAY_SYSTEM_ID'] == $revoPaysysId) {

            if ($arFields['PRICE'] && $order['PRICE'] && $order['PRICE'] != $arFields['PRICE']) {
                $revoClient = Instalment::getInstance();
                $result = $revoClient->change($id, $order);
                Logger::log($result, 'change');
            }
        }
    }

    public static function onSalePaymentPaid(Main\Event $event)
    {
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        $payment = $event->getParameter('ENTITY');
        /**
         * @var $payment Sale\Payment
         */
        $revoPaysysId = Option::get($moduleID, 'paysys_id', 0);

        if ($payment->getPaymentSystemId() == $revoPaysysId) {
            $order = \CSaleOrder::GetById($payment->getOrderId());
            $revoClient = Instalment::getInstance();
            $order['PRICE'] = $payment->getSum();

            $result = $revoClient->change($payment->getOrderId(), $order);
            Logger::log($result, 'change');
        }
    }

    public function appendJQuery() {
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        if (Option::get($moduleID, 'JQuery_selector', 'Y') == 'Y') {
            \CJSCore::init(array('jquery'));
        }
    }
}
