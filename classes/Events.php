<?php

namespace Revo;

use Bitrix\Conversion\DayContext;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;
use Revo\Models\RegisteredUsersTable;

class Events
{
    public function onProlog()
    {
        global $USER;
        if (Loader::includeModule('a.revo')) {
            $a = \Bitrix\Main\Page\Asset::getInstance();
            $a->addJs('/bitrix/js/a.revo/script.js');
            $a->addJs(Instalment::getInstance()->getEndpoint() . '/javascripts/iframe/v2/revoiframe.js');
            $a->addString('<link href="/bitrix/css/a.revo/modal.css" type="text/css" rel="stylesheet" />');
            $a->addString('<script>REVO_PAY_SYSTEM_ID=' . intval(Option::get('a.revo', 'paysys_id', 0)) . ';</script>');
            $a->addString('<script>REVO_MIN_PRICE=' . intval(Option::get('a.revo', 'detail_min_price', 0)) . ';</script>');
			$a->addString('<script>REVO_MAX_PRICE=' . intval(Option::get('a.revo', 'detail_max_price', 0)) . ';</script>');
            $a->addString('<script>REVO_REQUEST_DECLINED=' . intval(RegisteredUsersTable::get(bitrix_sessid())['declined']) . ';</script>');
            $a->addString('<script>REVO_ADD_PRICE_BLOCK=' . intval(
                    Option::get('a.revo', 'debug_mode', 'Y') != 'Y' || $USER->IsAdmin()
                ) . ';</script>');
            $a->addString('<script>REVO_ORDERS_URL = "' .
                Option::get('a.revo', 'orders_url', '/personal/orders/') .
                '";</script>');
            \CJSCore::Init(array('a.revo'));
        }
    }

    public function onStatusOrder($id, $val)
    {
        IncludeModuleLangFile(__FILE__);
        $returnStatus = Option::get('a.revo', 'return_status', 'RP');
        $finalStatus = Option::get('a.revo', 'finalization_status', 'F');
        $revoPaysysId = Option::get('a.revo', 'paysys_id', 0);
        $revoAdminEmail = Option::get('a.revo', 'email', '');
        $order = \CSaleOrder::GetById($id);

        if ($order['PAY_SYSTEM_ID'] == $revoPaysysId) {
            if ($val == $finalStatus) {
                $revoClient = Instalment::getInstance();

                $pdfPath = '/upload/check/' . $id . '.pdf';
                $fullPdfPath = $_SERVER['DOCUMENT_ROOT'] . $pdfPath;
                \Revo\Documents::billToPDF($id, $fullPdfPath);


                $result = $revoClient->finalizeOrder(
                    $order['ID'],
                    $order['PRICE'],
                    $fullPdfPath
                );

                if ($result['status'] !== 'ok') {
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

                    throw new \Bitrix\Sale\UserMessageException(Loc::getMessage('REVO_FINALIZATION_ERROR'));
                }

                Logger::log([
                    'Finalization have been sent to REVO', $result
                ], 'finalization');

            } elseif ($val == $returnStatus) {
                $revoClient = Instalment::getInstance();
                $result = $revoClient->returnOrder($id, $order['PRICE']);
                Logger::log($result, 'cancel');

            }
        }
    }

    public function onCancelOrder($id, $is_cancel, $description)
    {
        IncludeModuleLangFile(__FILE__);
        $revoPaysysId = Option::get('a.revo', 'paysys_id', 0);
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
        IncludeModuleLangFile(__FILE__);
        $revoPaysysId = Option::get('a.revo', 'paysys_id', 0);
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
        $payment = $event->getParameter('ENTITY');
        /**
         * @var $payment Sale\Payment
         */
        $revoPaysysId = Option::get('a.revo', 'paysys_id', 0);

        if ($payment->getPaymentSystemId() == $revoPaysysId) {
            $order = \CSaleOrder::GetById($payment->getOrderId());
            $revoClient = Instalment::getInstance();
            $order['PRICE'] = $payment->getSum();

            $result = $revoClient->change($payment->getOrderId(), $order);
            Logger::log($result, 'change');
        }
    }


}