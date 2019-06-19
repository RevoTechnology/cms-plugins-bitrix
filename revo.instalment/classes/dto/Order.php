<?php

namespace Revo\Dto;

use Bitrix\Main\Config\Option;

class Order
{
    public $callback_url;
    public $redirect_url;
    public $primary_phone;
    public $primary_email;
    public $additional_data;

    /**
     * @var OrderData
     */
    public $current_order;
    /**
     * @var Person
     */
    public $person;
    public $cart_items;

    public function __construct($email, $phone, OrderData $order, Person $person, $arCart = [])
    {
        $this->callback_url = Option::get(
            'revo.instalment',
            'callback_url'
        );
        $this->redirect_url = Option::get(
            'revo.instalment',
            'redirect_url'
        );
        $this->primary_phone = $phone;
        $this->primary_email = $email;
        $this->current_order = $order;
        $this->person = $person;
        $this->cart_items = [];
        foreach ($arCart as $cartItem) {
            $oCart = new OrderItem();
            $oCart->name = $cartItem['NAME'];
            $oCart->price = $cartItem['PRICE'];
            $oCart->quantity = $cartItem['QUANTITY'];
            $this->cart_items[] = $oCart;
        }
    }
}
