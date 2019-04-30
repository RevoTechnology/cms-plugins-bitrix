<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

define('INSTALMENT_PERIOD', 12);
?>
<a href="#" class="js-rvo-buy-link"
   data-item="<?=$arParams['ITEM_ID']?>"
   data-buybtn="<?=$arParams['BUY_BTN_SELECTOR']?>">
    Оплата частями от <?=round($arParams['PRICE'] / INSTALMENT_PERIOD)?> руб. в месяц.
</a>
