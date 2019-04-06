<?php

namespace Revo\Sdk;

class Config
{
    public $testMode = true;
    public $secret;
    public $storeId;
    public $baseHost;

    const TEST_ENDPOINT = 'https://backend.demo.revoup.ru/';
    const ENDPOINT = 'https://r.revoplus.ru';

    public function __construct($options = array())
    {
        if(
            !isset($options['secret'])      ||
            !isset($options['storeId'])     ||
            !isset($options['testMode'])
        )
        {
            throw new Error((object)['status' => 0, 'message' => 'Invalid config']);
        }

        $this->secret       = $options['secret'];
        $this->storeId      = $options['storeId'];
        $this->testMode     = $options['testMode'];

        $this->baseHost = ( $this->testMode ? self::TEST_ENDPOINT : self::ENDPOINT );
    }

}
