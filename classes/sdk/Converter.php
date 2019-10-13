<?php

namespace Revo\Sdk;

use Bitrix\Main\Text\Encoding;

class Converter
{
    public static function convertObjectToUtf($object) {
        $object = (array)$object;

        foreach ($object as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $object[$key] = self::convertObjectToUtf($value);
            } else {
                $object[$key] = Encoding::convertEncoding(
                    $value, SITE_CHARSET, 'UTF-8'
                );
            }
        }

        return $object;
    }
}
