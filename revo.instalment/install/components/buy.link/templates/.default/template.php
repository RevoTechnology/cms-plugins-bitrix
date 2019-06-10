<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
use Bitrix\Main\Config\Option;

$showBlock = Option::get('revo.instalment', 'debug_mode', 'Y') != 'Y' || $USER->IsAdmin();
if ($showBlock) {
    define('INSTALMENT_PERIOD', 12);
    ?>
    <a href="#" class="js-rvo-buy-link"
       data-item="<?= $arParams['ITEM_ID'] ?>"
       data-buybtn="<?= $arParams['BUY_BTN_SELECTOR'] ?>">
        Оплата частями от <?= round($arParams['PRICE'] / INSTALMENT_PERIOD) ?> руб. в месяц.
    </a>
    <?php
}