<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main\Localization\Loc;
\Bitrix\Main\Loader::includeModule('revo.instalment');

Loc::loadLanguageFile(__FILE__);
$paysystem = CSalePaySystem::GetById(\Bitrix\Main\Config\Option::get('revo.instalment', 'paysys_id'));

try {
    $url = \Revo\Instalment::getInstance()
        ->getOrderIframeUri(
            $GLOBALS["SALE_INPUT_PARAMS"],
            'http' . (CMain::IsHTTPS() ? 's':'') . '://' . SITE_SERVER_NAME . '/personal/orders/'
        );
    if ($paysystem['NEW_WINDOW'] == 'Y'):
        ?>
        <script>
            window.open('<?=$url?>');
        </script>
    <?else:?>
        <script>
            document.addEventListener('revo_modal_ready', function (e) {
                revoShowModal(false, '<?=$url?>');
            }, false);
        </script>
    <?endif?>
<?} catch (\Revo\Sdk\Error $e) {
    ?>
    <p><?=$e->getMessage()?></p>
    <?
}