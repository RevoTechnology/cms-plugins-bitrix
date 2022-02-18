<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main\Localization\Loc;
use Revo\Helpers\Extensions;
use Bitrix\Main\Config\Option;

$extension = new Extensions();
$moduleID = $extension->getModuleID();
\Bitrix\Main\Loader::includeModule($moduleID);

Loc::loadLanguageFile(__FILE__);
$paysystem = CSalePaySystem::GetById(\Bitrix\Main\Config\Option::get($moduleID, 'paysys_id'));

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$uri = new \Bitrix\Main\Web\Uri($request->getRequestUri());
$arUrlParams = $uri->getQuery();
$search = "/?" . $arUrlParams;
$uriWithoutParams = str_replace($search, "/", $uri);
$uriWithOrderId = $APPLICATION->sDirPath . $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"];

$display = 'block';?>
<?php if ($uriWithoutParams == $uriWithOrderId):
    $display = 'none';
    ?>
    <style>
        .button-revo-pre-modal {
            flex: 1 1 auto;
            margin: 10px;
            padding: 20px;
            /*border: 2px solid #f7f7f7;*/
            text-align: center;
            text-transform: uppercase;
            position: relative;
            overflow: hidden;
            transition: 0.3s;
            background: #ff4900;
        }
        .button-revo-pre-modal:after {
            position: absolute;
            transition: 0.3s;
            content: "";
            width: 0;
            left: 50%;
            bottom: 0;
            height: 3px;
            background: #f7f7f7;
        }
        .button-revo-pre-modal:hover {
            cursor: pointer;
            border-radius: 30px;
        }
        .container-revo-pre-modal {
            display: flex;
            justify-content: center;
            align-items: center;
            align-content: center;
            flex-wrap: wrap;
            width: 60%;
            max-width: 1200px;
            margin: 0 auto;
        }
        .span-revo-pre-modal {
            color: #FFF;
        }
    </style>
    <div class="container-revo-pre-modal">
        <a onclick="revoModal().style.display = 'block';">
            <div class="button-revo-pre-modal">
                <span class="span-revo-pre-modal"><?=GetMessage("REVO_PAYMENT_PHRASE_01")?><b>Mokka</b></span>
            </div>
        </a>
    </div>
<?php endif;?>

<?php
try {
    $ordersUrl = Option::get($moduleID, 'orders_url', '/personal/orders/');
    $url = \Revo\Instalment::getInstance()
        ->getOrderIframeUri(
            $GLOBALS["SALE_INPUT_PARAMS"],
            'http' . (CMain::IsHTTPS() ? 's':'') . '://' . SITE_SERVER_NAME . $APPLICATION->GetCurUri()
        );
    if ($paysystem['NEW_WINDOW'] == 'Y'):
        ?>
        <script>
            window.open('<?=$url?>');
        </script>
    <?elseif($uriWithoutParams != $ordersUrl):?>
        <script>
            revoShowModal(true, '<?=$url?>', '<?=$display?>');
            <?php
            /* при следующем обновлении модуля комментарии ниже можно удалить! (если вдруг помешают)
             если оставить срабатываение функции по событию - то кнопка "Оплатить" на странице заказа не работает
             предполагаю что проверка на срабатывание события излишняя. $ordersUrl получили? Отлично, показываем окно оплаты. */
            ?>
            // document.addEventListener('revo_modal_ready', function (e) {
            //    revoShowModal(false, '<?=$url?>');
            // }, false);
        </script>
    <?endif?>
<?} catch (\Revo\Sdk\Error $e) {
    ?>
    <p><?=$e->getMessage()?></p>
    <?
}
