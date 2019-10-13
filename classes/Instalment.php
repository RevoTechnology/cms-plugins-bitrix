<?php

namespace Revo;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use CSaleOrder;
use CSaleOrderPropsValue;
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

    const MODULE_ID = 'a.revo';

    private function _log($el) {

    }

    private function _getOption($optName, $default = false) {
        try {
            return Option::get(self::MODULE_ID, $optName, $default);
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
            }
        }

        return $this->_client->registration(
            $u['PERSONAL_PHONE'],
            $u['EMAIL'],
            $u['NAME'],
            $u['LAST_NAME'],
            $backurl
        );
    }

    public function getOrderIframeUri($globalOrderParams, $backurl = false) {
        global $USER;
        $orderId = $globalOrderParams['ORDER']['ID'];

        if (!isset($_SESSION['REVO_SAVED_ORDER_URI'])) $_SESSION['REVO_SAVED_ORDER_URI'] = [];

        if (array_key_exists($orderId, $_SESSION['REVO_SAVED_ORDER_URI']) && $_SESSION['REVO_SAVED_ORDER_URI'][$orderId]) {
            return $_SESSION['REVO_SAVED_ORDER_URI'][$orderId];
        }

        $u = \CUser::GetByID($USER->GetID())->Fetch();
        Loader::includeModule('sale');
        $rs = \CSaleBasket::GetList([], [
            'ORDER_ID' => $orderId
        ]);
        $arCart = [];
        while ($ar = $rs->Fetch()) {
            $arCart[] = $ar;
        }
        $order = new Dto\Order(
            $u['EMAIL'],
            $u['PERSONAL_PHONE'],
            Dto\OrderData::getFromGlobalParams($globalOrderParams),
            Dto\Person::getFromGlobalParams($globalOrderParams),
            $arCart
        );

        if ($backurl) $order->redirect_url = $backurl;

        $_SESSION['REVO_SAVED_ORDER_URI'][$orderId] = $this->_client->orderIframeLink($order);
        return $_SESSION['REVO_SAVED_ORDER_URI'][$orderId];
    }

    public function finalizeOrder($orderId, $sum, $filePath) {
        return $this->_client->finalizeOrder($orderId, $sum, $filePath);
    }

    public function returnOrder($orderId, $sum) {
        return $this->_client->returnOrder($sum, $orderId);
    }

    public function getEndpoint() {
        return $this->_endpoint;
    }
}