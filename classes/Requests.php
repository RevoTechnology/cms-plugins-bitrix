<?php

namespace Revo;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Revo\Helpers\Extensions;

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
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);


		file_put_contents('/home/mokka/mokka.604.ru/logs/'.$moduleID.'.log', "Revo\Requests\post: url ".json_encode($url)."\n", FILE_APPEND);
		file_put_contents('/home/mokka/mokka.604.ru/logs/'.$moduleID.'.log', "Revo\Requests\post: fields ".json_encode($fields)."\n", FILE_APPEND);
		file_put_contents('/home/mokka/mokka.604.ru/logs/'.$moduleID.'.log', "Revo\Requests\post: response ".json_encode($response)."\n", FILE_APPEND);


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

        return json_decode($response);
    }

    public static function statusOrder($orderId, $sum, $cnt) {
        if ($cnt <= 10) {
            $extension = new Extensions();
            $moduleID = $extension->getModuleID();
            $revoAdminEmail = Option::get($moduleID, 'email', '');

            $pdfPath = '/upload/check/' . $orderId . '.pdf';
            $fullPdfPath = $_SERVER['DOCUMENT_ROOT'] . $pdfPath;

            $revoClient = Instalment::getInstance();
            $result = $revoClient->finalizeOrder(
                $orderId,
                $sum,
                $fullPdfPath
            );

            if($result['result'] != 'error') {
                Logger::log([
                    'Finalization have been sent to REVO', $result
                ], 'finalization');
            }

            // если финализация не прошла
            if ($result['result'] == 'error') {
                // уведомляем админов партнера о том что финализация не прошла
                if ($revoAdminEmail) {
                    bxmail(
                        $revoAdminEmail,
                        Loc::getMessage('REVO_FINALIZATION_ERROR'),
                        str_replace(
                            ['#ERROR#', '#ORDER#'],
                            [$result['msg'], $orderId],
                            Loc::getMessage('REVO_ERROR_TEXT'))
                    );
                }
                // аналогично уведомляем РЕВО
                bxmail(
                    "integration@revo.ru",
                    Loc::getMessage('REVO_FINALIZATION_ERROR'),
                    str_replace(
                        ['#ERROR#', '#ORDER#', '#URL#'],
                        [$result['msg'], $orderId, $_SERVER['HTTP_ORIGIN']],
                        Loc::getMessage('REVO_ERROR_TEXT_2'))
                );
                Logger::log([
                    "Заказ ID = " . $orderId . " не прошел финализацию в РЕВО. \nПопыток финализировать: " . $cnt
                ], 'finalization-failed');
                $cnt++;
                // попробуем еще раз через час
                return "\Revo\Requests::statusOrder($orderId, $sum, $cnt);";
            } else {
                return "";
            }
        } else {
            return "";
        }
    }

    public static function writeLogAgent($cnt1, $cnt2) {
        Logger::log([
            "Agent is worked! + " . $cnt2*2
        ], 'writeLogAgent');
        if($cnt1>=5) {
            return "";
        }
        $cnt1++;
        $cnt2++;
        return "\Revo\Requests::writeLogAgent($cnt1, $cnt2);";
    }

    public static function test($arg1, $arg2, $arg3, $arg4) {
        $pdfPath = '/upload/check/' . $id . '.pdf';
        $fullPdfPath = $_SERVER['DOCUMENT_ROOT'] . $pdfPath;
        $arg1++;
        if($arg1>=5) {
            return "";
        }
        return "\Revo\Requests::test($arg1, $arg2, $arg3," . ($arg4) . ");";
    }
}