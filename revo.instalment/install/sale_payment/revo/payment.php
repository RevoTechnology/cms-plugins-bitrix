<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main\Localization\Loc;
\Bitrix\Main\Loader::includeModule('revo.instalment');

Loc::loadLanguageFile(__FILE__);
try {
    $url = \Revo\Instalment::getInstance()
        ->getOrderIframeUri(
            $GLOBALS["SALE_INPUT_PARAMS"],
            'http' . (CMain::IsHTTPS() ? 's':'') . '://' . SITE_SERVER_NAME . '/personal/orders/'
        );
    ?>
    <script>
        window.open('<?=$url?>');
    </script>
<?} catch (\Revo\Sdk\Error $e) {
    ?>
    <p><?=$e->getMessage()?></p>
    <?
}