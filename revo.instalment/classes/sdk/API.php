<?php

namespace Revo\Sdk;

class API
{
    private $api;

    public function __construct(Config $config)
    {
        $this->api = new Base($config);
    }

    public function limitByPhone($phone)
    {
        $data = $this->api->limitData($phone);
        $response = $this->api->callService($data, 'phone');
        $result = $this->api->parsePhoneResponse($response);

        return $result;
    }

    public function preorderIframeLink($phone = '')
    {
        $additionalParams = (empty($phone)) ? [] : [ 'primary_phone' => $phone ];

        $data = $this->api->orderData(1, bin2hex(openssl_random_pseudo_bytes(10)), $additionalParams);
        $response = $this->api->callService($data, 'preorder');
        $result = $this->api->parseOrderResponse($response);

        return $result;
    }

    public function orderIframeLink($amount, $orderId, $additionalParams = array())
    {
        $data = $this->api->orderData($amount, $orderId, $additionalParams);
        $response = $this->api->callService($data, 'order');
        $result = $this->api->parseOrderResponse($response);

        return $result;
    }

    public function returnOrder($amount, $orderId)
    {
        $data = $this->api->returnData($amount, $orderId);
        $response = $this->api->callService($data, 'return');
        $result = $this->api->parseReturnResponse($response);

        return $result;
    }
}
