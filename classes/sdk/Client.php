<?php

namespace Revo\Sdk;

use Revo\Requests;

class Client
{
    private $config;
    private $ENDPOINTS = [
        'return' => '/online/v1/return',
        'phone' => '/api/external/v1/client/limit',
        'preorder' => '/factoring/v1/limit/auth',
        'order' => '/factoring/v1/precheck/auth',
        'finish' => '/factoring/v1/precheck/finish',
        'status' => '/factoring/v1/status',
        'schedule' => '/factoring/v1/schedule',
        'change' => '/factoring/v1/precheck/change',
        'cancel' => '/factoring/v1/precheck/cancel'
    ];

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function limitData($phone)
    {
        return json_encode(['client' => ['mobile_phone' => $phone]]);
    }

    public function orderData($amount, $orderId, $additionalParams = [])
    {
        $payload = [
            'callback_url' => $this->config->callbackUrl,
            'redirect_url' => $this->config->redirectUrl,
            'current_order' => [
                'amount' => number_format($amount, 2, '.', ''),
                'order_id' => $orderId
            ]
        ];

        $data = (empty($additionalParams)) ? $payload : array_merge($payload, $additionalParams);

        return json_encode($data);
    }

    public function returnData($amount, $orderId)
    {
        return json_encode(['order_id' => $orderId, 'sum' => number_format($amount, 2, '.', ''), 'kind' => 'cancel']);
    }

    public function callService($data, $type, $files = false)
    {
        $signature = $this->sign($files ? $data['body'] : $data);
        $query = ['store_id' => $this->config->storeId, 'signature' => $signature];

        try {
            if (!$files) {
                $response = Requests::post(
                    $this->buildUrl($type, $query),
                    $data
                );
            } else {
                $response = Requests::post_with_files(
                    $this->buildUrl($type, $query),
                    $data,
                    $files
                );
            }

            if ($response->status) {
                throw new Error((object)['status' => $response->status_code, 'message' => $response->message ? $response->message : "Can't connect to API host"]);
            }

            return $response;
        } catch (\Exception $e) {
            \Revo\Logger::log([$this->buildUrl($type, $query), $data, $response], 'requests');
            throw new Error([], $e);
        }
    }

    public function parsePhoneResponse($data)
    {
        if ($data->meta->status == 0) {
            return $data->client;
        } else {
            throw new Error($data);
        }
    }

    public function parseOrderResponse($data)
    {
        if ($data->status == 0) {
            return $data->iframe_url;
        } else {
            throw new Error($data);
        }
    }

    public function parseReturnResponse($data)
    {
        if ($data->status === 0) {
            return ['status' => 'ok', 'data' => $data];
        } else {
            throw new Error($data);
        }
    }

    private function sign($data)
    {
        return sha1($data . $this->config->secret);
    }

    private function buildUrl($service_type, $query)
    {
        return $this->config->baseHost . $this->ENDPOINTS[$service_type] . '?' . http_build_query($query);
    }
}
