<?php
namespace Revo\Models;

use \Bitrix\Main\Entity;
use Revo\Logger;

class DataManager extends \Bitrix\Main\Entity\DataManager
{

    protected static $nullableFields = [];


    public static function getNullableFields(){
        return static::$nullableFields;
    }

    /**
     * @param bool $saveData
     */
    public static function reinstallTable($saveData = true){

        $arFieldsForAdd = [];
        $arFieldsForUpdate = [];
        $arFieldsForDelete = [];
        $tableName = static::getTableName();
        $arTableMap = static::getMap();
        $connection = static::getEntity()->getConnection();

        if($connection->isTableExists($tableName) && !$saveData){
            $connection->dropTable($tableName);
        }

        if(!$connection->isTableExists($tableName)){
            $connection->createTable($tableName, [$arTableMap[0]]);
            $arFieldsForUpdate[] = $arTableMap[0];
        }


        $arTableFields = $connection->getTableFields($tableName);

        $arMapFields = [];


        foreach ($arTableMap as $k => $mapField){
            /**
             * @var $mapField Entity\Field
             */
            $fieldName = $mapField->getName();
            $arMapFields[$fieldName] = $k;
            /**
             * @var $tableField Entity\Field
             */
            $tableField = $arTableFields[$fieldName];

            if(!array_key_exists($fieldName, $arTableFields)){
                $arFieldsForAdd[] = $mapField;
                continue;
            }


            $tfClass = get_class($tableField);
            if(!($mapField instanceof $tfClass)){

                if($mapField instanceof Entity\EnumField && $tableField instanceof Entity\StringField)
                    continue;

                $arFieldsForUpdate[] = $mapField;
                continue;
            }
        }



        foreach ($arTableFields as $fieldName => $tableField){
            if(!array_key_exists($fieldName, $arMapFields)){
                $arFieldsForDelete[] = $tableField;
                continue;
            }
        }

        self::installColumns($arFieldsForAdd, $arFieldsForDelete, $arFieldsForUpdate);
    }


    private static function installColumns($arFieldsForAdd, $arFieldsForDelete, $arFieldsForUpdate){

        $connection = static::getEntity()->getConnection();
        $connection->startTransaction();

        try{
            foreach ($arFieldsForAdd as $field){
                /**
                 * @var $field Entity\Field
                 */
                self::addColumn($field);
            }

            foreach ($arFieldsForDelete as $field){
                /**
                 * @var $field Entity\Field
                 */
                $connection->dropColumn(static::getTableName(), $field->getName());
            }

            foreach ($arFieldsForUpdate as $field){
                /**
                 * @var $field Entity\Field
                 */
                $connection->dropColumn(static::getTableName(), $field->getName());
                self::addColumn($field);
            }

            $connection->commitTransaction();

        } catch (\Exception $exception){
            $connection->rollbackTransaction();
            Logger::log($exception->getMessage(), 'data_manager');
        }
    }



    public static function addColumn($field){

        if(!($field instanceof Entity\ScalarField))
            return;

        $connection = static::getEntity()->getConnection();
        $fieldName = $field->getColumnName()?:$field->getName();

        $addFieldQuery = 'ALTER TABLE '.$connection->getSqlHelper()->quote(static::getTableName()).' ADD '.$connection->getSqlHelper()->quote($fieldName).' '.$connection->getSqlHelper()->getColumnTypeByField($field);

        $addFieldQuery .= ($field->isUnique())? ' UNIQUE':'';
        $addFieldQuery .= ($field->isPrimary())? ' PRIMARY KEY':'';
        $addFieldQuery .= (in_array($field->getName(), static::$nullableFields)) || $field->isRequired() === false ? '' : ' NOT NULL';
        $addFieldQuery .= ($field->isAutocomplete())?' AUTO_INCREMENT':'';


        try{
            $connection->query($addFieldQuery);
        } catch (\Bitrix\Main\Db\SqlQueryException $exception){
            echo $exception->getMessage();
        }
    }


    public static function dropTableIfExist(){

        $connection = static::getEntity()->getConnection();
        $tableName = static::getTableName();

        if($connection->isTableExists($tableName))
            $connection->dropTable($tableName);
    }


    public static function getFields(){

        $arFields = [];
        foreach (static::getMap() as $field){
            /**
             * @var $field Entity\ScalarField
             */

            $fieldName = ($field instanceof Entity\ReferenceField)? $field->getName() : $field->getColumnName();
            $arFields[$fieldName] = ($field instanceof Entity\ReferenceField)?'reference':$field->getDataType();
        }

        return $arFields;
    }


    public static function getEnumFieldValues(){
        $arFieldValues = [];
        foreach (static::getMap() as $field){
            /**
             * @var $field Entity\ScalarField
             */
            if($field->getDataType() == 'enum'){
                /**
                 * @var $field Entity\EnumField
                 */
                $arFieldValues[$field->getColumnName()] = $field->getValues();
            }
        }
        return $arFieldValues;
    }
}