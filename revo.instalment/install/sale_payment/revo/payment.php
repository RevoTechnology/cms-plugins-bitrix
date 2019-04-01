<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main\Localization\Loc;
\Bitrix\Main\Loader::includeModule('revo.instalment');

Loc::loadLanguageFile(__FILE__);
$url = \Revo\Instalment::getInstance()->getIframeUri();
?>
<iframe src="<?=$url?>" style="width: 100%;height: 400px;"></iframe>