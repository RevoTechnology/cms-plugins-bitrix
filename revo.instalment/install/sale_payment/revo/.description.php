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
    "DATE_INSERT" => array(
        "NAME" => GetMessage("SBLP_DATE"),
        "DESCR" => GetMessage("SBLP_DATE_DESC"),
        "VALUE" => "DATE_INSERT",
        "TYPE" => "ORDER"
    ),


    //bill document
    "DATE_PAY_BEFORE" => array(
        "NAME" => GetMessage("SBLP_PAY_BEFORE"),
        "DESCR" => GetMessage("SBLP_PAY_BEFORE_DESC"),
        "VALUE" => "DATE_PAY_BEFORE",
        "TYPE" => "ORDER"
    ),
    "SELLER_NAME" => array(
        "NAME" => GetMessage("SBLP_SUPPLI"),
        "DESCR" => GetMessage("SBLP_SUPPLI_DESC"),
        "VALUE" => "",
        "TYPE" => ""
    ),
    "SELLER_ADDRESS" => array(
        "NAME" => GetMessage("SBLP_ADRESS_SUPPLI"),
        "DESCR" => GetMessage("SBLP_ADRESS_SUPPLI_DESC"),
        "VALUE" => "",
        "TYPE" => ""
    ),
    "SELLER_PHONE" => array(
        "NAME" => GetMessage("SBLP_PHONE_SUPPLI"),
        "DESCR" => GetMessage("SBLP_PHONE_SUPPLI_DESC"),
        "VALUE" => "",
        "TYPE" => ""
    ),
    "SELLER_EMAIL" => array(
        "NAME" => GetMessage("SBLP_EMAIL_SUPPLI"),
        "DESCR" => GetMessage("SBLP_EMAIL_SUPPLI_DESC"),
        "VALUE" => "",
        "TYPE" => ""
    ),

    "SELLER_BANK_ACCNO" => array(
        "NAME" => GetMessage("SBLP_BANK_ACCNO_SUPPLI"),
        "DESCR" => GetMessage("SBLP_BANK_ACCNO_SUPPLI_DESC"),
        "VALUE" => GetMessage("SBLP_BANK_ACCNO_SUPPLI_VAL"),
        "TYPE" => ""
    ),
    "SELLER_BANK" => array(
        "NAME" => GetMessage("SBLP_BANK_SUPPLI"),
        "DESCR" => GetMessage("SBLP_BANK_SUPPLI_DESC"),
        "VALUE" => GetMessage("SBLP_BANK_SUPPLI_VAL"),
        "TYPE" => ""
    ),
    "SELLER_BANK_BLZ" => array(
        "NAME" => GetMessage("SBLP_BANK_BLZ_SUPPLI"),
        "DESCR" => GetMessage("SBLP_BANK_BLZ_SUPPLI_DESC"),
        "VALUE" => GetMessage("SBLP_BANK_BLZ_SUPPLI_VAL"),
        "TYPE" => ""
    ),

    "SELLER_EU_INN" => array(
        "NAME" => GetMessage("SBLP_EU_INN_SUPPLI"),
        "DESCR" => GetMessage("SBLP_EU_INN_SUPPLI_DESC"),
        "VALUE" => "",
        "TYPE" => ""
    ),
    "SELLER_INN" => array(
        "NAME" => GetMessage("SBLP_INN_SUPPLI"),
        "DESCR" => GetMessage("SBLP_INN_SUPPLI_DESC"),
        "VALUE" => "",
        "TYPE" => ""
    ),
    "SELLER_REG" => array(
        "NAME" => GetMessage("SBLP_REG_SUPPLI"),
        "DESCR" => GetMessage("SBLP_REG_SUPPLI_DESC"),
        "VALUE" => "",
        "TYPE" => ""
    ),
    "SELLER_DIR" => array(
        "NAME" => GetMessage("SBLP_DIR_SUPPLI"),
        "DESCR" => GetMessage("SBLP_DIR_SUPPLI_DESC"),
        "VALUE" => "",
        "TYPE" => ""
    ),
    "SELLER_ACC" => array(
        "NAME" => GetMessage("SBLP_ACC_SUPPLI"),
        "DESCR" => GetMessage("SBLP_ACC_SUPPLI_DESC"),
        "VALUE" => "",
        "TYPE" => ""
    ),

    "BUYER_NAME" => array(
        "NAME" => GetMessage("SBLP_CUSTOMER"),
        "DESCR" => GetMessage("SBLP_CUSTOMER_DESC"),
        "VALUE" => "COMPANY_NAME",
        "TYPE" => "PROPERTY",
    ),
    "BUYER_ADDRESS" => array(
        "NAME" => GetMessage("SBLP_CUSTOMER_ADRES"),
        "DESCR" => GetMessage("SBLP_CUSTOMER_ADRES_DESC"),
        "VALUE" => "ADDRESS",
        "TYPE" => "PROPERTY"
    ),
    "BUYER_PHONE" => array(
        "NAME" => GetMessage("SBLP_CUSTOMER_PHONE"),
        "DESCR" => GetMessage("SBLP_CUSTOMER_PHONE_DESC"),
        "VALUE" => "PHONE",
        "TYPE" => "PROPERTY"
    ),
    "BUYER_FAX" => array(
        "NAME" => GetMessage("SBLP_CUSTOMER_FAX"),
        "DESCR" => GetMessage("SBLP_CUSTOMER_FAX_DESC"),
        "VALUE" => "FAX",
        "TYPE" => "PROPERTY"
    ),
    "BUYER_PAYER_NAME" => array(
        "NAME" => GetMessage("SBLP_CUSTOMER_PERSON"),
        "DESCR" => GetMessage("SBLP_CUSTOMER_PERSON_DESC"),
        "VALUE" => "PAYER_NAME",
        "TYPE" => "PROPERTY"
    ),
    "COMMENT1" => array(
        "NAME" => GetMessage("SBLP_COMMENT1"),
        "DESCR" => "",
        "VALUE" => GetMessage("SBLP_COMMENT1_VALUE"),
        "TYPE" => ""
    ),
    "COMMENT2" => array(
        "NAME" => GetMessage("SBLP_COMMENT2"),
        "DESCR" => "",
        "VALUE" => "",
        "TYPE" => ""
    ),
    "PATH_TO_LOGO" => array(
        "NAME" => GetMessage("SBLP_LOGO"),
        "DESCR" => GetMessage("SBLP_LOGO_DESC"),
        "VALUE" => "",
        "TYPE" => "FILE"
    ),
    "PATH_TO_STAMP" => array(
        "NAME" => GetMessage("SBLP_PRINT"),
        "DESCR" => GetMessage("SBLP_PRINT_DESC"),
        "VALUE" => "",
        "TYPE" => "FILE"
    ),
    "SELLER_DIR_SIGN" => array(
        "NAME" => GetMessage("SBLP_DIR_SIGN_SUPPLI"),
        "DESCR" => GetMessage("SBLP_DIR_SIGN_SUPPLI_DESC"),
        "VALUE" => "",
        "TYPE" => "FILE"
    ),
    "SELLER_ACC_SIGN" => array(
        "NAME" => GetMessage("SBLP_ACC_SIGN_SUPPLI"),
        "DESCR" => GetMessage("SBLP_ACC_SIGN_SUPPLI_DESC"),
        "VALUE" => "",
        "TYPE" => "FILE"
    ),
    "BACKGROUND" => array(
        "NAME" => GetMessage("SBLP_BACKGROUND"),
        "DESCR" => GetMessage("SBLP_BACKGROUND_DESC"),
        "VALUE" => "",
        "TYPE" => "FILE"
    ),
    "BACKGROUND_STYLE" => array(
        "NAME" => GetMessage("SBLP_BACKGROUND_STYLE"),
        "DESCR" => "",
        "VALUE" => array(
            'none' => array('NAME' => GetMessage("SBLP_BACKGROUND_STYLE_NONE")),
            'tile' => array('NAME' => GetMessage("SBLP_BACKGROUND_STYLE_TILE")),
            'stretch' => array('NAME' => GetMessage("SBLP_BACKGROUND_STYLE_STRETCH"))
        ),
        "TYPE" => "SELECT"
    ),
    "MARGIN_TOP" => array(
        "NAME" => GetMessage("SBLP_MARGIN_TOP"),
        "DESCR" => "",
        "VALUE" => "15",
        "TYPE" => ""
    ),
    "MARGIN_RIGHT" => array(
        "NAME" => GetMessage("SBLP_MARGIN_RIGHT"),
        "DESCR" => "",
        "VALUE" => "15",
        "TYPE" => ""
    ),
    "MARGIN_BOTTOM" => array(
        "NAME" => GetMessage("SBLP_MARGIN_BOTTOM"),
        "DESCR" => "",
        "VALUE" => "15",
        "TYPE" => ""
    ),
    "MARGIN_LEFT" => array(
        "NAME" => GetMessage("SBLP_MARGIN_LEFT"),
        "DESCR" => "",
        "VALUE" => "20",
        "TYPE" => ""
    )
);