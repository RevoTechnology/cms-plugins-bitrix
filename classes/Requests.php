<?php

namespace Revo;

/**
 * Class API
 * @package Revo
 */
class Requests
{
    /**
     * POST request
     * @param $url
     * @param $fields
     * @return array
     */
    public static function post($url, $fields)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);

        Logger::log([$url, $fields], 'request');
        Logger::log($response, 'request');

        return json_decode($response);
    }

    public static function post_with_files($url, $fields, $filenames){
        $boundary = uniqid();
        $files = array();
        foreach ($filenames as $key => $f){
            $files[$key] = [
                'content' => file_get_contents($f),
                'file_name' => array_pop(explode(DIRECTORY_SEPARATOR, $f))
            ];
        }

        $data = '';
        $eol = "\r\n";

        $delimiter = '-------------' . $boundary;

        foreach ($fields as $name => $content) {
            $data .= "--" . $delimiter . $eol
                . 'Content-Disposition: form-data; name="' . $name . "\"".$eol.$eol
                . $content . $eol;
        }


        foreach ($files as $name => $content) {
            $data .= "--" . $delimiter . $eol
                . 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $content['file_name'] . '"' . $eol
                . 'Content-Transfer-Encoding: base64'.$eol
                . 'Content-Type: application/pdf'.$eol
            ;

            $data .= $eol;
            $data .= base64_encode($content['content']) . $eol;
        }
        $data .= "--" . $delimiter . "--".$eol;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: multipart/form-data; boundary=" . $delimiter,
                "Content-Length: " . strlen($data)
            ),
        ));

        $response = curl_exec($curl);

        Logger::log([$url, $fields, $filenames], 'request');
        Logger::log($response, 'request');

        return $response;
    }
}