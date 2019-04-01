<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main\Localization\Loc;
Loc::loadLanguageFile(__FILE__);
$url = \Revo\Instalment::getInstance()->getIframeUri();
?>
<iframe src="<?=$url?>"></iframe>