<?php

namespace Revo\Dto;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Revo\Helpers\Extensions;
use Revo\Instalment;

class Order
{
    private $config;

    public $callback_url;
    public $redirect_url;
    public $primary_phone;
    public $primary_email;

    /**
     * @var OrderData
     */
    public $current_order;
    /**
     * @var Person
     */
    public $person;

    private function _getOption($optName, $default = false) {
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        try {
            return Option::get($moduleID, $optName, $default);
        } catch (ArgumentException $e) {
            return false;
        }
    }

    public function __construct($type, $email, $phone, $address, OrderData $order, Person $person, $arCart = [])
    {
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        $this->callback_url = Option::get(
            $moduleID,
            'callback_url'
        );
        $this->redirect_url = Option::get(
            $moduleID,
            'redirect_url'
        );
        $this->primary_phone = (string)$phone;
        $this->primary_email = $email;
        $this->current_order = $order;
        $this->person = $person;
        // если Order нужен не для checkout - больше не наполняем объект данными
        if ($type != 'order')
            return;

        $this->cart_items = [];
        //TODO: Сделать возможность настраивать этот параметр в настройках модуля
        $this->skip_result_page = false;
        $arPurchase = [];
        foreach ($arCart as $cartItem) {
            $oCart = new OrderItem();
            $oCart->name = $cartItem['NAME'];
            $oCart->price = (float)$cartItem['BASE_PRICE'];
            if ($cartItem['PRICE'] != $cartItem['BASE_PRICE'])
                $oCart->sale_price = (float)$cartItem['PRICE'];
            $oCart->quantity = (int)$cartItem['QUANTITY'];
            $this->cart_items[] = $oCart;

            $breadcrumbs = "";
            // получаем ID товара по ID торгового предложения
            $productInfo = \CCatalogSku::GetProductInfo($cartItem['PRODUCT_ID']);
            // получаем товар по его ID
            $rsElement = \CIBlockElement::GetList(array(), array('ID' => $productInfo['ID']), false, false);
            if($arElement = $rsElement->Fetch())
            {
                // получаем цепочку категорий для данного товара
                $categories = \CIBlockSection::GetNavChain(false, $arElement['IBLOCK_SECTION_ID'], array(), true);
                // проходим по массиву категорий, формируем цепочку в одну переменную
                $cnt = 0;
                foreach ($categories as $category) {
                    if ($cnt == 0)
                        $breadcrumbs .= trim($category['NAME']);
                    else
                        $breadcrumbs .= " > ".trim($category['NAME']);
                    $cnt++;
                }
            }

            $discount = 0;
            if ($cartItem["DISCOUNT_PRICE"] != 0) {
                $discount = $cartItem['DISCOUNT_PRICE'] / ($cartItem['BASE_PRICE'] / 100);
            }
            $arPurchase[] = [
                'product_name' => $cartItem['NAME'],
                'number' => (int)$cartItem['QUANTITY'],
                'product_price' => (float)$cartItem['BASE_PRICE'],
                'discount' => $discount."%",
                'breadcrumbs'=> $breadcrumbs,
            ];
        }


        // пользователь из глобальных параметров
        $user = $GLOBALS["SALE_INPUT_PARAMS"]['USER'];

        // заказ из глобальных параметров
        $orderGlobal = $GLOBALS["SALE_INPUT_PARAMS"]['ORDER'];

        // платеж из глобальных параметров
        $paymentGlobal = $GLOBALS["SALE_INPUT_PARAMS"]['PAYMENT'];

        // дополнительные параметры из глобальных параметров
        $propertyGlobal = $GLOBALS["SALE_INPUT_PARAMS"]['PROPERTY'];

        // оплаченные заказы пользователя
        $cntOrders = 0;
        $sumOrders = 0;
        $first_purchase_date = null;
        $last_purchase_date = null;
        $lastOrderId = null;

        $finalStatus = $this -> _getOption('finalization_status', 'F');
        $arFilter = array("USER_ID" => $user['ID'], "STATUS_ID" => $finalStatus);
        $db_sales = \CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), $arFilter);
        while ($ar_sales = $db_sales->Fetch()) {
            if ($cntOrders == 0)
                $first_purchase_date = ConvertDateTime($ar_sales['DATE_PAYED'], "DD.MM.YYYY");
            if (ConvertDateTime($ar_sales['DATE_PAYED'], "DD.MM.YYYY") != false)
                $last_purchase_date = ConvertDateTime($ar_sales['DATE_PAYED'], "DD.MM.YYYY");
            $sumOrders+=$ar_sales["PRICE"];
            $cntOrders++;
            $lastOrderId = $ar_sales["ID"];
        }

        // Является ли клиент повторным для партнёра
        $returning_customer = false;
        if ($cntOrders > 0)
            $returning_customer = true;

        // Является ли последним способом оплаты банковская карта
        $bank_card = null;

        // оплаченные заказы пользователя за последние 6 месяцев
        $last_orders = 0;
        $current_time = AddToTimeStamp(array("MM" => -6), getmicrotime());
        $six_month_ago_date = ConvertTimeStamp($current_time, 'SHORT');

        $arFilter = array("USER_ID" => $user['ID'], "PAYED" => "Y", ">=DATE_PAYED" => $six_month_ago_date);
        $db_sales = \CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), $arFilter);
        while ($ar_sales = $db_sales->Fetch()) {
            $last_orders++;
        }

        // Совпадает ли текущий адрес доставки с предыдущим адресом доставки
        $same_address = null;
        if ($lastOrderId != null) {
            $arFilter = ["ORDER_ID" => $lastOrderId];
            $properties = \CSaleOrderPropsValue::GetList(array(), $arFilter);
            while ($property = $properties->Fetch())
            {
                if ($property["CODE"] == "ADDRESS") {
                    if ($propertyGlobal["ADDRESS"] == $property["VALUE"])
                        $same_address = true;
                    else
                        $same_address = false;
                }
            }
        }

        $gender = null;
        if (!empty($user['PERSONAL_GENDER']))
            $gender = $user['PERSONAL_GENDER'];

        $birthdate = null;
        if (!empty($user['PERSONAL_BIRTHDAY']))
            $birthdate = $user['PERSONAL_BIRTHDAY'];

        $data_change_date = null;
        if (!empty($user['TIMESTAMP_X']))
            $data_change_date = ConvertDateTime($user['TIMESTAMP_X'], "DD.MM.YYYY");

        $client = [
            'phone' => (string)$phone,
            'email' => $user['EMAIL'],
            'client_id' => (string)$user['ID'],
            'registration_date' => ConvertDateTime($user['DATE_REGISTER'], "DD.MM.YYYY"),
            'data_change_date' => $data_change_date,
            'purchases_volume' => (int)$cntOrders,
            'purchases_sum' => (float)$sumOrders,
            'last_purchase_date' => $last_purchase_date,
            'first_purchase_date' => $first_purchase_date,
            'birthdate' => $birthdate,
            'gender' => $gender,
        ];

        $orderAdditional = [
            //'country' => 'RU',
            'currency' => strtolower($paymentGlobal['CURRENCY']),
            'order_price' => $paymentGlobal['SUM'],
        ];
        $delivery = [
            //'delivery_kind' => 'store pick-up',
            'receiver_name' => $propertyGlobal["FIO"],
            'delivery_address' => $address,
        ];
        $purchase = $arPurchase;

        $this->additional_data = [
            //'previous_url' => 'https://revo.ru/where-to-buy',
            //'channel' => 'mobile',
            'returning_customer' => $returning_customer, //bool
            //'bank_card' => $bank_card, //bool
            'last_orders' => (string)$last_orders, //string
            'same_address' => $same_address, //bool
            'client' => $client, //array
            'order' => $orderAdditional, //array
            'delivery' => $delivery, //array
            'purchase' => $purchase, //array
        ];
    }
}
