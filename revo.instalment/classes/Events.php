<?php

namespace Revo;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Revo\Models\RegisteredUsersTable;

class Events
{
    public function onProlog()
    {
        global $USER;
        if (Loader::includeModule('a.revo')) {
            $a = \Bitrix\Main\Page\Asset::getInstance();
            $a->addJs('/local/modules/a.revo/js/script.js');
            $a->addJs(Instalment::getInstance()->getEndpoint() . '/javascripts/iframe/v2/revoiframe.js');
            $a->addString('<link href="/local/modules/a.revo/css/modal.css" type="text/css" rel="stylesheet" />');
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
        $revoPaysysId = Option::get('a.revo', 'paysys_id', 0);
        $order = \CSaleOrder::GetById($id);

        if ($order['PAY_SYSTEM_ID'] == $revoPaysysId) {
            if ($val == 'F') {
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
}