<?php

namespace Revo;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
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

    const MODULE_ID = 'revo.instalment';

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
        $u = \CUser::GetByID($USER->GetID())->Fetch();
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
        $u = \CUser::GetByID($USER->GetID())->Fetch();
        Loader::includeModule('sale');
        $rs = \CSaleBasket::GetList([], [
            'ORDER_ID' => $globalOrderParams['ORDER']['ID']
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

        return $this->_client->orderIframeLink($order);
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