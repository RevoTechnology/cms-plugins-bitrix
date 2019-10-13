<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
    "PARAMETERS" => array(
        "PRODUCT_ID" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("REVO_BUY_PARAM_PRODUCT_ID"),
            "TYPE" => "STRING",
            "DEFAULT" => '={$_REQUEST["ELEMENT_ID"]}'
        ),
        "PRICE" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("REVO_BUY_PARAM_PRICE"),
            "TYPE" => "STRING",
        ),
        "BUY_BTN_SELECTOR" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("REVO_BUY_PARAM_BTN_SELECTOR"),
            "TYPE" => "STRING",
        ),
    ),
);

