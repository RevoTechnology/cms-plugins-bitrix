<?php

namespace Revo\Sdk;

use Bitrix\Main\Application;
use Revo\Dto\Order;
use Revo\Dto\OrderData;
use Revo\Dto\OrderDataUpdate;
use Revo\Dto\Person;
use Revo\Logger;

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
            new OrderData(bitrix_sessid() . ":" . uniqid(), 1),
            new Person($name, $last_name, '')
        );

        Logger::log($order, 'data');

        if ($backurl) $order->redirect_url = $backurl;
        if (!Application::getInstance()->isUtfMode()) {
            $order = Converter::convertObjectToUtf($order);
        }
        $order = json_encode($order);
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

        // check order status
        $status = $this->api->callService($data, 'status');

        // order exists
        if ($status && $status->current_order) {
            // cancel limit holding
            if ($status->current_order->status == 'hold')
                $response = $this->api->callService($data, 'cancel');
            else if ($status->current_order->status == 'finished')
                $response = $this->api->callService($data, 'return');
        }

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

    public function changeOrder(OrderDataUpdate $orderUpdateData)
    {
        try {
            $data = json_encode($orderUpdateData);

            $response = $this->api->callService($data, 'change');
            $result = $this->api->parseReturnResponse($response);
        } catch (Error $e) {
            return [
                'result' => 'error',
                'msg' => $e->getMessage()
            ];
        }


        return $result;
    }

    public function getTariffs($amount)
    {
        try {
            $data = json_encode(['amount' => $amount]);

            $response = $this->api->callService($data, 'schedule');
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
