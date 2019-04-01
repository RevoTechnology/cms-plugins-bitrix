<?php

namespace Revo\Sdk;

class Config
{
    public $testMode = true;
    public $callbackUrl;
    public $redirectUrl;
    public $secret;
    public $storeId;
    public $baseHost;

    public function __construct($options = array())
    {
        if(
            !isset($options['callbackUrl']) ||
            !isset($options['redirectUrl']) ||
            !isset($options['secret'])      ||
            !isset($options['storeId'])     ||
            !isset($options['testMode'])
        )
        {
            throw new Error((object)['status' => 0, 'message' => 'Invalid config']);
        }

        $this->callbackUrl  = $options['callbackUrl'];
        $this->redirectUrl  = $options['redirectUrl'];
        $this->secret       = $options['secret'];
        $this->storeId      = $options['storeId'];
        $this->testMode     = $options['testMode'];

        $this->baseHost = ( $this->testMode ? 'https://backend.demo.revoup.ru/' : 'https://r.revoplus.ru' );
    }

}
