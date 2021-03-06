<?php

namespace Revo;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use CSaleOrder;
use CSaleOrderPropsValue;
use Revo\Dto\Order;
use Revo\Dto\OrderData;
use Revo\Dto\OrderItem;
use Revo\Dto\Person;
use Revo\Helpers\Extensions;
use Revo\Sdk\Config;

class Instalment
{
    /**
     * @var self $instance
     */
    static $instance;

    private $_config;
    private $_client;

    private $_endpoint;


    private function _log($el) {

    }

    private function _getOption($optName, $default = false) {
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        try {
            return Option::get($moduleID, $optName, $default);
        } catch (ArgumentException $e) {
            return false;
        }
    }

    private function __construct()
    {
        try {
            $testMode = $this->_getOption('debug_mode', 'Y') != 'N';
            $this->_config = new Sdk\Config(
                [
                    'testMode' => $testMode,
                    'redirectUrl' => $this->_getOption('redirect_url', 'http://example.com/'),
                    'callbackUrl' => $this->_getOption('callback_url', 'http://example.com/'),
                    'storeId' => $this->_getOption('api_merchant', 204),
                    'secret' => $this->_getOption('api_secret', '6279a164f5cb8bbe93f7')
                ]
            );

            $this->_client = new Sdk\API($this->_config);
            $this->_endpoint = $testMode ? Config::TEST_ENDPOINT : Config::ENDPOINT;
        } catch (Sdk\Error $e) {
            $this->_log('SDK building error: '. $e->getMessage());
        }
    }

    public static function getInstance() {
        if (!static::$instance) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    public function getRegistrationUri($backurl = false) {
        global $USER;
        \CModule::IncludeModule('sale');
        $u = \CUser::GetByID($USER->GetID())->Fetch();
        $arOrder = CSaleOrder::GetList(['ID' => 'DESC'], [
            'USER_ID' => $USER->GetID()
        ])->Fetch();
        if ($arOrder) {
            $rsProps = CSaleOrderPropsValue::getList(
                [],
                [
                    'ORDER_ID' => $arOrder['ID']
                ]
            );
            while ($ar = $rsProps->Fetch()) {
                if ($ar['CODE'] == 'FIO' && !$u['LAST_NAME']) {
                    list(
                        $u['LAST_NAME'],
                        $u['NAME'],
                        $u['SECOND_NAME']
                        ) = explode(' ', $ar['VALUE']);
                }
                if (in_array($ar['CODE'], ['EMAIL'])) {
                    $u[$ar['CODE']] = $ar['VALUE'];
                }
                if (in_array($ar['CODE'], ['PHONE'])) {
                    $u['PERSONAL_PHONE'] = $ar['VALUE'];
                }
                if (in_array($ar['CODE'], ['ADDRESS'])) {
                    $u['ADDRESS'] = $ar['VALUE'];
                }
            }
        }

        return $this->_client->registration(
            $u['PERSONAL_PHONE'],
            $u['EMAIL'],
            $u['NAME'],
            $u['LAST_NAME'],
            $u['ADDRESS'],
            $backurl
        );
    }

    public function getOrderIframeUri($globalOrderParams) {
        global $USER;
        $orderId = $globalOrderParams['ORDER']['ID'];

        if (!isset($_SESSION['REVO_SAVED_ORDER_URI']))
            $_SESSION['REVO_SAVED_ORDER_URI'] = [];

        if (array_key_exists($orderId, $_SESSION['REVO_SAVED_ORDER_URI']) && $_SESSION['REVO_SAVED_ORDER_URI'][$orderId])
            return $_SESSION['REVO_SAVED_ORDER_URI'][$orderId];

        $u = \CUser::GetByID($USER->GetID())->Fetch();
        Loader::includeModule('sale');
        $rs = \CSaleBasket::GetList([], [
            'ORDER_ID' => $orderId
        ]);
        $arCart = [];
        while ($ar = $rs->Fetch()) {
            $arCart[] = $ar;
        }

        $order = new Dto\Order("order",
            $globalOrderParams['PROPERTY']['EMAIL'] ?: $u['EMAIL'],
            $globalOrderParams['PROPERTY']['PHONE'] ?: $u['PERSONAL_PHONE'],
            $globalOrderParams['PROPERTY']['ADDRESS'],
            Dto\OrderData::getFromGlobalParams($globalOrderParams),
            Dto\Person::getFromGlobalParams($globalOrderParams),
            $arCart
        );
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        $order->redirect_url = Option::get($moduleID, 'redirect_url', '/personal/');
        $_SESSION['REVO_SAVED_ORDER_URI'][$orderId] = $this->_client->orderIframeLink($order);
        return $_SESSION['REVO_SAVED_ORDER_URI'][$orderId];
    }

    public function finalizeOrder($orderId, $sum, $filePath) {
        return $this->_client->finalizeOrder($orderId, $sum, $filePath);
    }

    public function returnOrder($orderId, $sum) {
        return $this->_client->returnOrder($sum, $orderId);
    }

    public function change($order) {
        $basket = $order->getBasket();
        $arCart = [];
        foreach ($basket as $item) {
            $oCart = new OrderItem();
            $oCart->name = $item->getField('NAME');
            $oCart->price = $item->getField('PRICE');
            $oCart->quantity = $item->getField('QUANTITY');

            $arCart[] = $oCart;
        }

        $orderUpdateData = new Dto\OrderDataUpdate(
            $order->getId(),
            $order->getPrice(),
            null,
            $arCart
        );
        return $this->_client->changeOrder($orderUpdateData);
    }

    public function getTariffs($amount) {
        return $this->_client->getTariffs($amount);
    }

    public function getEndpoint() {
        return $this->_endpoint;
    }
}