<?php

namespace Revo;

use Bitrix\Main\Config\Option;

class Events
{
    public function onProlog()
    {
        $a = \Bitrix\Main\Page\Asset::getInstance();
        $a->addJs('/local/modules/revo.instalment/js/script.js');
        $a->addJs(Instalment::getInstance()->getEndpoint() . '/javascripts/iframe/v2/revoiframe.js');
        $a->addString('<link href="/local/modules/revo.instalment/css/modal.css" type="text/css" rel="stylesheet" />');
        $a->addString('<script>REVO_PAY_SYSTEM_ID=' . intval(Option::get('revo.instalment', 'paysys_id', 0)) . ';</script>');
        $a->addString('<script>REVO_MIN_PRICE=' . intval(Option::get('revo.instalment', 'detail_min_price', 0)) . ';</script>');
        $a->addString('<script>REVO_ORDERS_URL = "' .
            Option::get('revo.instalment', 'orders_url', '/personal/orders/') .
            '";</script>');

        \CJSCore::Init(array('revo.instalment'));
    }

    public function onStatusOrder($id, $val)
    {

        if ($val == 'F') {
            $revoPaysysId = Option::get('revo.instalment', 'paysys_id', 0);
            $order = \CSaleOrder::GetById($id);
            if ($order['PAY_SYSTEM_ID'] == $revoPaysysId) {
                $revoClient = Instalment::getInstance();

                $pdfPath = '/upload/check/' . $id . '.pdf';
                $fullPdfPath = $_SERVER['DOCUMENT_ROOT'] . $pdfPath;
                \Revo\Documents::billToPDF($id, $fullPdfPath);

                $result = $revoClient->finalizeOrder($order['ID'], $order['SUM_PAID'], $fullPdfPath);

                Logger::log([
                    'Finalization have been sent to REVO', $result
                ], 'finalization');
            }
        }
    }
}