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

    /**
     * Сначала берем ФИО из личного кабинета пользователя.
     * Если там нет - берем из формы.
     * Отчество почти всегда будет браться из формы.
     * $arName[0] - фамилия
     * $arName[1] - имя
     * $arName[2] - отчество
     */
    public static function getFromGlobalParams($arParams)
    {
        // ФИО которое вводят на этапе оформления заказа
        $arName = explode(' ', $arParams['PROPERTY']['FIO']);

        $obj = new self('', '', '');
        // фамилия
        if (!empty($arParams['USER']['LAST_NAME']))
            $obj->surname = $arParams['USER']['LAST_NAME'];
        elseif(!empty($arName[0]))
            $obj->surname = $arName[0];

        // имя
        if (!empty($arParams['USER']['NAME']))
            $obj->first_name = $arParams['USER']['NAME'];
        elseif(!empty($arName[1]))
            $obj->first_name = $arName[1];

        // отчество
        if (!empty($arParams['USER']['SECOND_NAME']))
            $obj->patronymic = $arParams['USER']['SECOND_NAME'];
        elseif(!empty($arName[2]))
            $obj->patronymic = $arName[2];

        return $obj;
    }
}
