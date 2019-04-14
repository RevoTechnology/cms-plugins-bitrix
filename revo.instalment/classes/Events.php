<?php

namespace Revo;

use Bitrix\Main\Config\Option;

class Events
{
    public function onProlog() {
        $a = \Bitrix\Main\Page\Asset::getInstance();
        $a->addJs('/local/modules/revo.instalment/js/script.js');
        $a->addJs(Instalment::getInstance()->getEndpoint() . '/javascripts/iframe/v2/revoiframe.js');
        $a->addString('<link href="/local/modules/revo.instalment/css/modal.css" type="text/css" rel="stylesheet" />');
        $a->addString('<script>REVO_PAY_SYSTEM_ID='.intval(Option::get('revo.instalment', 'paysys_id', 0)).';</script>');

        \CJSCore::Init(array('revo.instalment'));
    }

    public function onStatusOrder($id, $val) {

        if ($val == 'F') {
            $revoPaysysId = Option::get('revo.instalment', 'paysys_id', 0);
            $order = \CSaleOrder::GetById($id);
            if ($order['PAY_SYSTEM_ID'] == $revoPaysysId) {
                $revoClient = Instalment::getInstance();

                $html = \Revo\Documents::printCheck($id);
                $path = '/upload/check/'.$id.'.pdf';
                \Revo\Documents::convertHtmlToPdf(
                    \Bitrix\Main\Text\Encoding::convertEncoding(
                        $html, SITE_CHARSET, 'cp1251'
                    ),
                    $path
                );
                $fullPath = $_SERVER['DOCUMENT_ROOT'] . $path;

                $result = $revoClient->finalizeOrder($order['ID'], $order['SUM_PAID'], $fullPath);
                Logger::log([
                    'Finalization have been sent to REVO', $result
                ], 'finalization');
            }
        }
    }
}