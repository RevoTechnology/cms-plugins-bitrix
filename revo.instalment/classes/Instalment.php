<?php

namespace Revo;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;

class Instalment
{
    /**
     * @var self $instance
     */
    static $instance;

    private $_config;
    private $_client;

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
            $this->_config = new Sdk\Config(
                [
                    'testMode' => $this->_getOption('debug_mode', 'Y') == 'N' ? false : true,
                    'redirectUrl' => $this->_getOption('redirect_url', 'http://example.com/'),
                    'callbackUrl' => $this->_getOption('callback_url', 'http://example.com/'),
                    'storeId' => $this->_getOption('api_merchant', 204),
                    'secret' => $this->_getOption('api_secret', '6279a164f5cb8bbe93f7')
                ]
            );

            $this->_client = new Sdk\API($this->_config);

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
        $order = new Dto\Order(
            $globalOrderParams['USER']['EMAIL'],
            $globalOrderParams['USER']['PERSONAL_PHONE'],
            Dto\OrderData::getFromGlobalParams($globalOrderParams),
            Dto\Person::getFromGlobalParams($globalOrderParams)
        );

        if ($backurl) $order->redirect_url = $backurl;

        return $this->_client->orderIframeLink($order);
    }
}