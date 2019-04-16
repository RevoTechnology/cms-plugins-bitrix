<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
?>
<a href="#" class="js-rvo-buy-link"
   data-item="<?=$arParams['ITEM_ID']?>" data-buybtn="<?=$arParams['BUY_BTN_SELECTOR']?>">Купить за <?=round($arParams['MONTHLY_PRICE'] / 3)?> руб. в месяц</a>
