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
                    'testMode' => $this->_getOption('debug_mode', true),
                    'redirectUrl' => $this->_getOption('redirect_url', 'http://example.com/'),
                    'callbackUrl' => $this->_getOption('callback_url', 'http://example.com/'),
                    'storeId' => $this->_getOption('store_id', 204),
                    'secret' => $this->_getOption('secret', '6279a164f5cb8bbe93f7')
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

    public function getIframeUri() {
        return $this->_client->preorderIframeLink();
    }
}