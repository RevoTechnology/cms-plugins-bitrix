<?php

namespace Revo;

/**
 * Class Instalment
 * @package Revo
 */
class API
{
    const API_URL = '';

    /**
     * POST request
     * @param $url
     * @param $fields
     * @return array
     */
    public static function post($url, $fields)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::API_URL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = (array)json_decode(curl_exec($ch));

        return $response;
    }
}