<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

define('INSTALMENT_PERIOD', 12);
?>
<a href="#" class="js-rvo-buy-link"
   data-item="<?= $arParams['ITEM_ID'] ?>"
   data-buybtn="<?= $arParams['BUY_BTN_SELECTOR'] ?>">
    <?=GetMessage('REVO_BUY_PART_BUY')?> <?= CurrencyFormat($arResult['AMOUNT'] ?: round($arParams['PRICE'] / INSTALMENT_PERIOD), 'RUB') ?>
    <?=GetMessage('REVO_BUY_PART_BUY_POSTFIX')?>
</a>