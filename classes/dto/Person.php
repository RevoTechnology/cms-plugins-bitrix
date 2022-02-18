<?php

namespace Revo\Dto;

class Person
{
    public $first_name;
    public $surname;
    public $patronymic;

    public function __construct($first_name, $surname, $patronymic)
    {
        $this->first_name = $first_name;
        $this->surname = $surname;
        $this->patronymic = $patronymic;
    }

    public static function getFromGlobalParams($arParams)
    {
        $obj = new self('', '', '');
        $obj->first_name = $arParams['PROPERTY']['FIO'] ? array_pop(explode(' ', $arParams['PROPERTY']['FIO'], 1)) : $arParams['USER']['NAME'];
        $obj->surname = $arParams['PROPERTY']['FIO'] && strpos($arParams['PROPERTY']['FIO'], ' ') ?
            array_shift(explode(' ', $arParams['PROPERTY']['FIO'], 1)) : $arParams['USER']['LAST_NAME'];
        return $obj;
    }
}
