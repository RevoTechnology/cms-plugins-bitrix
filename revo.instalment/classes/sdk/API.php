<?php

namespace Revo\Sdk;

use Bitrix\Main\Application;
use Revo\Dto\Order;
use Revo\Dto\OrderData;
use Revo\Dto\Person;

class API
{
    private $api;

    public function __construct(Config $config)
    {
        $this->api = new Client($config);
    }

    public function limitByPhone($phone)
    {
        $data = $this->api->limitData($phone);
        $response = $this->api->callService($data, 'phone');
        $result = $this->api->parsePhoneResponse($response);

        return $result;
    }

    public function registration(
        $phone = '',
        $email = '',
        $name = '',
        $last_name = '',
        $backurl = false
    )
    {
        $order = new Order(
            $email,
            $phone,
            new OrderData(bitrix_sessid(), 1),
            new Person($name,$last_name,'')
        );

        if ($backurl) $order->redirect_url = $backurl;
        $order = \Bitrix\Main\Web\Json::encode($order);
        $response = $this->api->callService($order, 'preorder');
        $result = $this->api->parseOrderResponse($response);

        return $result;
    }

    public function orderIframeLink(Order $order)
    {
        if (!Application::getInstance()->isUtfMode()) {
            $order = Converter::convertObjectToUtf($order);
        }
        $order = json_encode($order);

        $response = $this->api->callService($order, 'order');
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

    public function finalizeOrder($orderId, $sum, $filePath)
    {
        try {
            $data = json_encode(['order_id' => $orderId, 'amount' => $sum]);
            $fields = ['body' => $data];
            $files = ['check' => $filePath];
            $response = $this->api->callService($fields, 'finish', $files);
            $result = $this->api->parseReturnResponse($response);
        } catch (Error $e) {
            return [
                'result' => 'error',
                'msg' => $e->getMessage()
            ];
        }


        return $result;
    }
}
