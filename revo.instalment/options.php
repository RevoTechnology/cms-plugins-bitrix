<?php

require_once('prolog.php');

if (!$USER->IsAdmin())
    return;

use \Bitrix\Main\Config\Option,
    \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Loader;

Loader::includeModule(ADMIN_MODULE_NAME);

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/options.php');
Loc::loadMessages(__FILE__);


$arAllOptions = array(
    ['detail_max_order_part', Loc::getMessage('OPTIONS_DETAIL_MAX_ORDER_PART'), '100', ['text', 200]],
    ['detail_min_price', Loc::getMessage('OPTIONS_DETAIL_MIN_PRICE'), '0', ['text', 200]],
    ['orders_url', Loc::getMessage('OPTIONS_ORDERS_URL'), '/personal/orders/', ['text', 200]],
);


$arRevoModuleOptions = [

    ['callback_url', Loc::getMessage('OPTIONS_API_CALLBACK'), '', ['text', 200]],
    ['redirect_url', Loc::getMessage('OPTIONS_API_REDIRECT'), '', ['text', 200]],
    ['api_merchant', Loc::getMessage('OPTIONS_API_METCHANT'), '', ['text', 100]],
    ['api_secret', Loc::getMessage('OPTIONS_API_SECRET'), '', ['text', 100]],
    array('debug_mode', Loc::getMessage('OPTIONS_API_TEST_MODE'), 'Y', array('checkbox', 'Y')),
];

$aTabs = [
    [
        'DIV' => 'general_options',
        'TAB' => Loc::getMessage('REVO_OPTIONS_TAB'),
        'TITLE' => Loc::getMessage('REVO_OPTIONS_TITLE'),
    ],
    [
        'DIV' => 'api_options',
        'TAB' => Loc::getMessage('API_OPTIONS_TAB'),
        'TITLE' => Loc::getMessage('API_OPTIONS_TITLE'),
    ],
];
$tabControl = new CAdminTabControl('tabControl', $aTabs);

if ($_SERVER["REQUEST_METHOD"] == 'POST' && strlen($Update.$Apply.$RestoreDefaults) > 0 && check_bitrix_sessid()) {
    if (strlen($RestoreDefaults) > 0) {
        Option::delete(ADMIN_MODULE_NAME);
    } else {
        foreach (array_merge($arAllOptions, $arRevoModuleOptions) as $arOption) {
            $optionName = $arOption[0];
            $optionValue = $_REQUEST[$optionName];

            $fieldType = $arOption[3][0];

            if ($fieldType == 'checkbox' && $optionValue != 'Y')
                $optionValue = 'N';

            if ($optionName == 'detail_max_order_part') {

                if ($optionValue < 20 || $optionValue > 100) {
                    $APPLICATION->ThrowException(Loc::getMessage('OPTIONS_DETAIL_MAX_ORDER_PART_MIN'));
                    $optionValue = 100;
                }
            }
            Option::set(ADMIN_MODULE_NAME, $optionName, $optionValue);
        }
    }
}
?><?CAdminMessage::ShowOldStyleError($APPLICATION->GetException());?><?
$tabControl->Begin(); ?>

<form method="post" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<? echo LANGUAGE_ID ?>">
    <?
    $tabControl->BeginNextTab();

    foreach ($arAllOptions as $arOption) {
        $val = Option::get(ADMIN_MODULE_NAME, $arOption[0], $arOption[2]);
        $type = $arOption[3];
        ?>
        <tr>
            <td width="50%" class="adm-detail-content-cell-l<? if ($type[0] == 'textarea') echo ' adm-detail-valign-top' ?>">
                <label for="<?= htmlspecialcharsbx($arOption[0]) ?>"><?= $arOption[1] ?>:</label>
            </td>
            <td width="50%" class="adm-detail-content-cell-r">
                <? if ($type[0] == 'checkbox'): ?>
                    <input type="checkbox" id="<?= htmlspecialcharsbx($arOption[0]) ?>"
                           name="<?= htmlspecialcharsbx($arOption[0]) ?>"
                           value="Y"<? if ($val == 'Y') echo " checked"; ?>><?= $arOption[4] ?>
                <?
                elseif ($type[0] == 'text'): ?>
                    <input type="text" maxlength="<?= $type[1] ?>" value="<?= htmlspecialcharsbx($val) ?>"
                           name="<?= htmlspecialcharsbx($arOption[0]) ?>"><?= $arOption[4] ?>
                <?
                elseif ($type[0] == 'textarea'): ?>
                    <textarea rows="<?= $type[1] ?>" cols="<?= $type[2] ?>"
                              name="<?= htmlspecialcharsbx($arOption[0]) ?>"><?= htmlspecialcharsbx($val) ?><?= $arOption[4] ?></textarea>
                <?
                elseif ($type[0] == 'selectbox'): ?>
                    <select name="<?= htmlspecialcharsbx($arOption[0]) ?>" id="<?= htmlspecialcharsbx($arOption[0]) ?>">
                        <?
                        foreach ($arOption[4] as $v => $k) {
                            ?>
                            <option value="<?= $v ?>"<? if ($val == $v) echo " selected"; ?>><?= $k ?></option><?
                        }
                        ?>
                    </select>
                <?endif ?>
            </td>
        </tr>
        <?
    }


    $tabControl->BeginNextTab();

    foreach ($arRevoModuleOptions as $arOption){

        $val = Option::get(ADMIN_MODULE_NAME, $arOption[0], $arOption[2]);
        $type = $arOption[3];
        ?>
        <tr>
            <td width="50%" class="adm-detail-content-cell-l">
                <?=$arOption[1]?>
            </td>
            <td width="50%" class="adm-detail-content-cell-r">
                <? if ($type[0] == 'checkbox'): ?>
                    <input type="checkbox" id="<?= htmlspecialcharsbx($arOption[0]) ?>"
                           name="<?= htmlspecialcharsbx($arOption[0]) ?>"
                           value="Y"<? if ($val == 'Y') echo " checked"; ?>><?= $arOption[4] ?>
                <?
                elseif ($type[0] == 'text'): ?>
                    <input type="text" maxlength="<?= $type[1] ?>" value="<?= htmlspecialcharsbx($val) ?>"
                           name="<?= htmlspecialcharsbx($arOption[0]) ?>"><?= $arOption[4] ?>
                <?
                elseif ($type[0] == 'textarea'): ?>
                    <textarea rows="<?= $type[1] ?>" cols="<?= $type[2] ?>"
                              name="<?= htmlspecialcharsbx($arOption[0]) ?>"><?= htmlspecialcharsbx($val) ?><?= $arOption[4] ?></textarea>
                <?
                elseif ($type[0] == 'selectbox'): ?>
                    <select name="<?= htmlspecialcharsbx($arOption[0]) ?>" id="<?= htmlspecialcharsbx($arOption[0]) ?>">
                        <?
                        foreach ($arOption[4] as $v => $k) {
                            ?>
                            <option value="<?= $v ?>"<? if ($val == $v) echo " selected"; ?>><?= $k ?></option><?
                        }
                        ?>
                    </select>
                <?endif ?>
            </td>
        </tr>
        <?
    }

    $tabControl->Buttons();
    ?>
    <input type="submit" name="Update" value="<?=Loc::getMessage('MAIN_SAVE')?>"
           title="<?=Loc::getMessage('MAIN_OPT_SAVE_TITLE')?>" class="adm-btn-save">
    <input type="submit" name="Apply" value="<?=Loc::getMessage('MAIN_OPT_APPLY')?>"
           title="<?=Loc::getMessage('MAIN_OPT_APPLY_TITLE')?>">
    <? if(strlen($_REQUEST['back_url_settings']) > 0){ ?>
        <input type="button" name="Cancel" value="<?=Loc::getMessage('MAIN_OPT_CANCEL')?>"
               title="<?=Loc::getMessage('MAIN_OPT_CANCEL_TITLE')?>"
               onclick="window.location='<?=CUtil::addslashes($_REQUEST['back_url_settings'])?>'">
        <input type="hidden" name="back_url_settings" value="<?=$_REQUEST['back_url_settings']?>">
    <? } ?>
    <input type="submit" name="RestoreDefaults" title="<?=Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS')?>"
           OnClick="return confirm('<?=addslashes(Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>')"
           value="<?=Loc::getMessage('MAIN_RESTORE_DEFAULTS')?>">
    <?=bitrix_sessid_post();?>
    <?$tabControl->End();?>
</form>