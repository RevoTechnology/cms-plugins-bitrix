<?php

namespace Revo\Dto;

class OrderDataUpdate
{
    public $order_id;
    public $valid_till;
    public $amount;
    public $cart_items;

    public function __construct($id, $amount, $valid_till, $cart_items)
    {
        $this->order_id = $id;
        $this->amount = $amount;
        $this->valid_till = $valid_till;
        $this->cart_items = $cart_items;
    }
}
