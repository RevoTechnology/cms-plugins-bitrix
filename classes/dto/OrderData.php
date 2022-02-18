<?php

namespace Revo\Dto;

use Bitrix\Main\Config\Option;
use Revo\Helpers\Extensions;

class OrderData
{
    public $order_id;
    public $valid_till;
    public $term;
    public $amount;
    public $prepayment_amount;

    const PAYMENT_PART = 90;

    public function __construct($id, $amount)
    {
        $this->order_id = $id;
        $this->amount = $amount;
    }

    public static function getFromGlobalParams($arParams) {
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        $maxOrderPart = Option::get($moduleID, 'detail_max_order_part', 100);
        $obj = new self('', '');
        $obj->order_id = $arParams['ORDER']['ID'];
        $obj->amount = $arParams['PAYMENT']['SUM'] * $maxOrderPart / 100;
        return $obj;
    }
}
