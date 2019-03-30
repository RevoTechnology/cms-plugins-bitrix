<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);
$psTitle = "Revo Instalment";
$psDescription  = Loc::getMessage("T_PAYSYS_DESCRIPTION");

$arPSCorrespondence = array(
	"MERCHANT_ID" => Array(
		"NAME" => Loc::getMessage("T_PAYSYS_OPT_MERCHANTID"),
		"DESCR" => Loc::getMessage("T_PAYSYS_OPT_MERCHANTID_DESC"),
		"VALUE" => "",
		"TYPE" => "",
	),
	"ORDER_ID" => Array(
		"NAME" => Loc::getMessage("T_PAYSYS_OPT_ORDER_ID"),
		"DESCR" => Loc::getMessage("T_PAYSYS_OPT_ORDER_ID_DESC"),
		"VALUE" => "",
		"TYPE" => "ORDER",
	),
	"SHOULD_PAY" => Array(
		"NAME" => Loc::getMessage("T_PAYSYS_OPT_ORDER_SUM"),
		"DESCR" => "",
		"VALUE" => "",
		"TYPE" => "ORDER",
	),
	"CURRENCY" => Array(
		"NAME" => Loc::getMessage("T_PAYSYS_OPT_ORDER_VALUTE"),
		"DESCR" => "",
		"TYPE" => "SELECT",
		"VALUE" => array(
			"RUB" => array("NAME" => Loc::getMessage("T_VALUTE_RUB")),
			"USD" => array("NAME" => Loc::getMessage("T_VALUTE_BAKS")),
			"EUR" => array("NAME" => Loc::getMessage("T_VALUTE_EURO"))
		),
	),
	"SECURITY_KEY" => Array(
		"NAME" => Loc::getMessage("T_PAYSYS_OPT_MERCHANTID_PRIVATE_KEY"),
		"DESCR" => Loc::getMessage("T_PAYSYS_OPT_MERCHANTID_PRIVATE_KEY_DESC"),
		"VALUE" => "",
		"TYPE" => "",
	),
	"RETURN_URL" => Array(
		"NAME" => Loc::getMessage("T_PAYSYS_OPT_BACKPAGE_SUCCESS"),
		"DESCR" => "",
		"VALUE" => "",
		"TYPE" => "",
	),
	"FAIL_URL" => Array(
		"NAME" => Loc::getMessage("T_PAYSYS_OPT_BACKPAGE_FAILURE"),
		"DESCR" => "",
		"VALUE" => "",
		"TYPE" => "",
	),
);