<?php

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Config\Option;
use Revo\Helpers\Extensions;
?>

<style>
    @font-face {
        font-family: "Svyaznoy Sans";
        src: url("/local/components/revo/buy.link/img/Svyaznoy_Sans.otf");
        font-weight: normal;
        font-style: normal;
    }
    @font-face {
        font-family: "Svyaznoy Sans";
        src: url("/local/components/revo/buy.link/img/Svyaznoy_Sans-Light.ttf");
        font-weight: 200;
        font-style: normal;
    }

    @font-face {
        font-family: "Rubik";
        src: url("/local/components/revo/buy.link/img/Rubik-Regular.ttf");
        font-weight: normal;
        font-style: normal;
    }
    @font-face {
        font-family: "Odds";
        src: url("/local/components/revo/buy.link/img/Odds_Regular.otf");
        font-weight: normal;
        font-style: normal;
    }

    .price-module {
        display: inline-flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-content: center;
        align-items: center;
        background-color: #FF5A1E;
        color: white;
        font-family: "Svyaznoy Sans", sans-serif;
        line-height: 1;
        padding: 5px 12px;
        max-width: 360px;
        width: 100%;
        box-sizing: border-box;
        cursor: pointer;
    }
    .price-module * {
        color: white;
        font-family: "Svyaznoy Sans", sans-serif;
        line-height: 1;
        padding: 0;
        margin: 0;
        box-sizing: border-box;
    }

    .price-module__price {
        margin: 5px 0;
        display: flex;
        align-content: flex-end;
        align-items: flex-end;
        margin-right: 10px;
        white-space: nowrap;
        cursor: pointer;
        text-decoration: none;
        margin: auto;
    }
    .price-module__price p {
        margin-right: 4px;
        font-size: 14px;
        font-weight: bold;
    }
    .price-module__price strong {
        font-weight: bold;
        font-size: 16px;
    }

    .price-module__logo {
        margin: 5px 0;
        max-height: 18px;
        max-width: 60px;
        display: flex;
        justify-content: center;
        align-items: center;
        align-content: center;
    }
    .price-module__logo img {
        max-height: 100%;
        max-width: 100%;
    }

    .price-module__divider {
        margin: 5px 6px;
        width: 1px;
        height: 13px;
        background-color: white;
    }

    .price-module__payment-description {
        margin: 5px 0;
        margin-right: 6px;
        font-size: 16px;
        font-weight: 200;
        white-space: nowrap;
    }

    .price-module__modal-trigger {
        margin: 5px 0;
        cursor: pointer;
        border: none;
        outline: none;
        background: none;
        box-shadow: none;
        width: 20px;
        height: 20px;
        margin: auto;
    }
    .price-module__modal-trigger:focus {
        border: none;
        outline: none;
    }
    .price-module__modal-trigger img{
        max-height: 100%;
        max-width: 100%;
    }




    .price-module-modal {
        color: #323232;
        font-family: "Rubik", sans-serif;
        line-height: 1.5;
        font-weight: normal;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: none;
        z-index: 1000;
    }
    .price-module-modal.open {
        display: block;
    }
    .price-module-modal * {
        color: #323232;
        font-family: "Rubik", sans-serif;
        line-height: 1.5;
        font-weight: normal;
        padding: 0;
        margin: 0;
        box-sizing: border-box;
    }

    .price-module-modal__backdrop {
        background-color: black;
        opacity: 0.1;
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
        z-index: 1001;
    }

    .price-module-modal__modal {
        background-color: white;
        position: fixed;
        max-width: 744px;
        width: 100%;
        max-height: 100%;
        overflow-y: auto;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        box-shadow: 2px 2px 12px rgba(0, 0, 0, 0.3);
        padding: 14px 40px;
        padding-bottom: 32px;
        text-align: center;
        z-index: 1002;
    }

    .price-module-modal__modal-close {
        position: absolute;
        top: 37px;
        right: 28px;
        border: none;
        outline: none;
        background: none;
        cursor: pointer;
    }

    .price-module-modal__modal-content {
        width: 100%;
    }

    .price-module-modal__modal-content h3 {
        font-family: "Odds", sans-serif;
        font-size: 59px;
        text-transform: lowercase;
        color: #FF5A1E;
        padding: 0 20px;
        display: block;
    }

    .price-module-modal__advantages {
        list-style-type: none;
        display: flex;
        justify-content: space-between;
        max-width: 100%;
        margin-top: 30px;
    }
    .price-module-modal__advantages li {
        list-style: none;
        max-width: 180px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-content: center;
        align-items: center;
        flex-shrink: 0;
    }
    .price-module-modal__advantage-img-block {
        margin-bottom: 45px;
        max-width: 136px;
        max-height: 136px;
    }
    .price-module-modal__advantage-img-block img {
        max-width: 100%;
        max-height: 100%;
    }


    @media(max-width: 745px) {
        .price-module-modal__advantages {
            flex-wrap: wrap;
            justify-content: center;
        }
        .price-module-modal__advantages li {
            max-width: 250px;
            margin: 20px;
        }
    }
    @media(max-width: 678px) {
        .price-module-modal__advantages {
            flex-wrap: wrap;
            justify-content: center;
            flex-direction: column;
        }
        .price-module-modal__advantages li {
            max-width: 100%;
            margin: 20px 0;
        }
    }
    .mokka-group {
        display: flex;
        margin: 0 auto;
        align-items: center;
    }
</style>
<div class="price-module js-rvo-buy-link"
     data-item="<?= $arParams['ITEM_ID'] ?>"
     data-buybtn="<?= $arParams['BUY_BTN_SELECTOR'] ?>">
    <div class="mokka-group">
        <div class="price-module__logo">
            <img src= "/local/components/revo/buy.link/img/logo.svg" alt="Логотип">
        </div>
        <div class="price-module__divider"></div>
        <div class="price-module__payment-description"><?=GetMessage('REVO_BUY_PART_BUY')?></div>
        <button class="price-module__modal-trigger">
            <img src= "/local/components/revo/buy.link/img/question.svg" alt="Узнать больше">
        </button>
    </div>
</div>

<div class="price-module-modal">
    <div class="price-module-modal__backdrop"></div>
    <div class="price-module-modal__modal">
        <button class="price-module-modal__modal-close"><img src="/local/components/revo/buy.link/img/close.svg" alt="Закрыть окно"></button>
        <div class="price-module-modal__modal-content">
            <h3><?=GetMessage("REVO_POPUP_TEMPLATE_PHRASE_HEADER")?></h3>
            <?php
            $extension = new Extensions();
            $moduleID = $extension->getModuleID();
            ?>
            <strong><?=GetMessage("REVO_POPUP_TEMPLATE_PHRASE_01")?><br/><?=GetMessage("REVO_POPUP_TEMPLATE_PHRASE_02")?>
                <?=intval(Option::get($moduleID, 'detail_min_price', 0))?><?=GetMessage("REVO_POPUP_TEMPLATE_PHRASE_03")?></strong>
            <ul class="price-module-modal__advantages">
                <li>
                    <div class="price-module-modal__advantage-img-block">
                        <img src= "/local/components/revo/buy.link/img/advantage-1.png" alt="<?=GetMessage("REVO_POPUP_TEMPLATE_PHRASE_04")?>">
                    </div>
                    <div class="price-module-modal__advantage-description">
                        <p><?=GetMessage("REVO_POPUP_TEMPLATE_PHRASE_04")?></p>
                    </div>
                </li>
                <li>
                    <div class="price-module-modal__advantage-img-block">
                        <img src= "/local/components/revo/buy.link/img/advantage-2.png" alt="<?=GetMessage("REVO_POPUP_TEMPLATE_PHRASE_05")?>">
                    </div>
                    <div class="price-module-modal__advantage-description">
                        <p><?=GetMessage("REVO_POPUP_TEMPLATE_PHRASE_05")?></p>
                    </div>
                </li>
                <li>
                    <div class="price-module-modal__advantage-img-block">
                        <img src= "/local/components/revo/buy.link/img/advantage-3.png" alt="<?=GetMessage("REVO_POPUP_TEMPLATE_PHRASE_06")?>">
                    </div>
                    <div class="price-module-modal__advantage-description">
                        <p><?=GetMessage("REVO_POPUP_TEMPLATE_PHRASE_06")?></p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const priceModuleModalTriggerOpen = document.querySelector('.price-module__modal-trigger');
        const priceModuleModalTriggerClose = document.querySelector('.price-module-modal__modal-close');
        const priceModuleModalBackdrop = document.querySelector('.price-module-modal__backdrop');
        const priceModuleModal = document.querySelector('.price-module-modal');
        // askBtnPressed объявлен внутри script.js
        if(priceModuleModalTriggerOpen && priceModuleModal) {
            priceModuleModalTriggerOpen.addEventListener("click", () => {
                priceModuleModal.classList.add('open');
                askBtnPressed = true;
            });
        }
        if(priceModuleModalTriggerClose && priceModuleModal) {
            priceModuleModalTriggerClose.addEventListener("click", () => {
                priceModuleModal.classList.remove('open');
                askBtnPressed = false;
            });
        }
        if(priceModuleModalBackdrop && priceModuleModal) {
            priceModuleModalBackdrop.addEventListener("click", () => {
                priceModuleModal.classList.remove('open');
                askBtnPressed = false;
            });
        }
    });
</script>