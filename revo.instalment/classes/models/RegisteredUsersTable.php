<?php
namespace Revo\Models;

use \Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;

class RegisteredUsersTable extends DataManager
{
    public static function getConnectionName(){
        return 'default';
    }

    public static function getTableName()
    {
        return 'revo_registered_users';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('id', ['primary' => true, 'autocomplete' => true]),
            new Entity\DatetimeField('timestamp'),
            new Entity\StringField('sessid'),
            new Entity\BooleanField('approved'),
        );
    }

    public static function get($sessid) {
        return static::getList(['filter' => ['sessid' => $sessid], 'limit' => 1])->fetch();
    }

    public static function addUser($sessid) {
        $timestamp = new DateTime(ConvertTimeStamp(false, 'FULL'));
        $data = [
            'sessid' => $sessid,
            'timestamp' => $timestamp
        ];
        $addResult = self::add($data);

        return $addResult->isSuccess();
    }
}