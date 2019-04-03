<?php

namespace Revo;

use Bitrix\Main\Config\Option;

class Events
{
    public function onProlog() {
        $a = \Bitrix\Main\Page\Asset::getInstance();
        $a->addJs('/local/modules/revo.instalment/js/script.js');
        $a->addString('<script>REVO_PAY_SYSTEM_ID='.intval(Option::get('revo.instalment', 'paysys_id', 0)).';</script>');
    }
}