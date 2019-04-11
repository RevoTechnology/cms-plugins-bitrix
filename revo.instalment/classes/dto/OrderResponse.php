<?php

namespace Revo\Dto;

class OrderResponse
{
    public $order_id;
    public $decision;
    public $amount;
    public $term;
    public $client;
    public $schedule;
    public $monthly_overpayment;

    public function __construct(\stdClass $obj)
    {
        $obj = (array)$obj;
        foreach ($obj as $key => $val) {
            $this->{$key} = $val;
        }
    }
}
