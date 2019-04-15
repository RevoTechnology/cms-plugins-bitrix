<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
    'NAME' => GetMessage('REVO_BUY_NAME'),
    'DESCRIPTION' => GetMessage('REVO_BUY_DESC'),
    'SORT' => 110,
    'CACHE_PATH' => 'Y',
    'PATH' => array(
        'ID' => 'Revo',
        'CHILD' => array(
            'ID' => 'instalment',
            'NAME' => GetMessage('REVO_BUY_SECTION_NAME'),
            'SORT' => 110,
        ),
    ),
);