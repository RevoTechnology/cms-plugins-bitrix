<?php

namespace Revo\Dto;

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
        $obj = new self('', '');
        $obj->order_id = $arParams['ORDER']['ID'];
        $obj->amount = $arParams['PAYMENT']['SUM'] * self::PAYMENT_PART / 100;
        return $obj;
    }
}
