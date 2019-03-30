<?

use Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class revo_instalment extends CModule
{

    const OPTION_PAYSYS_ID = 'paysys_id';

    public $MODULE_ID;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;


    public function __construct()
    {
        $this->errors = false;
        $arModuleVersion = array();
        $path = str_replace('\\', '/', __FILE__);
        $path = substr($path, 0, strlen($path) - strlen('/index.php'));
        include($path . '/version.php');
        $this->MODULE_ID = 'revo.instalment';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = GetMessage('REVO_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('REVO_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = GetMessage('REVO_MODULE_PARTNER_NAME');
    }


    public function DoInstall()
    {
        global $USER;
        if (!\Bitrix\Main\Loader::includeModule('sale')) {
            $GLOBALS['errors'] = [
                'Даный плагин работает только с модулем "Интернет-магазин"'
            ];
            return;
        }
        if ($USER->IsAdmin()) {
            RegisterModule($this->MODULE_ID);
            $this->InstallFiles();
            $this->InstallEvents();
            $this->InstallDb();
            $GLOBALS['errors'] = $this->errors;
        }
    }


    public function DoUninstall()
    {
        global $USER;
        if ($USER->IsAdmin()) {
            $this->UnInstallFiles();
            $this->UnInstallEvents();
            $this->UnInstallDb();
            UnRegisterModule($this->MODULE_ID);
            $GLOBALS['errors'] = $this->errors;
            Option::delete($this->MODULE_ID);
        }
    }


    public function InstallEvents()
    {

    }

    public function UnInstallEvents()
    {

    }

    public function InstallDb()
    {
        \Bitrix\Main\Loader::includeModule('sale');
        $result = \Bitrix\Sale\Internals\PaySystemActionTable::add(
            array(
                'NAME' => 'Revo Instalment',
                'PSA_NAME' => 'Revo Instalment',
                'ACTIVE' => 'Y',
                'CAN_PRINT_CHECK' => 'N',
                'CODE' => '',
                'NEW_WINDOW' => 'Y',
                'ALLOW_EDIT_PAYMENT' => 'Y',
                'IS_CASH' => 'N',
                'SORT' => 100,
                'ENCODING' => 'utf-8',
                'DESCRIPTION' => 'Оформление рассрочки от REVO',
                'ACTION_FILE' => '/local/php_interface/include/sale_payment/revo',
                'PS_MODE' => '',
                'AUTO_CHANGE_1C' => 'N',
                'HAVE_PAYMENT' => 'Y'
            )
        );

        if (!$result->isSuccess()) {
            $this->errors[] = 'Не удалось добавить платежную систему.';
        } else {
            $paySystemId = $result->getId();

            Option::set(
                $this->MODULE_ID,
                self::OPTION_PAYSYS_ID,
                $paySystemId
            );

            $fields = array(
                'PARAMS' => serialize(
                    array('BX_PAY_SYSTEM_ID' => $paySystemId)
                ),
                'PAY_SYSTEM_ID' => $paySystemId
            );

            $result = \Bitrix\Sale\Internals\PaySystemActionTable::update(
                $paySystemId,
                $fields
            );

            $arParamsConsumer = [
                'CURRENCY' => [
                    'KEY' => 'INPUT',
                    'VALUE' => 'RUB'
                ],
                'FAIL_URL' => [
                    'KEY' => 'VALUE',
                    'VALUE' => '/shop/cart/fail.php'
                ],
                'RETURN_URL' => [
                    'KEY' => 'VALUE',
                    'VALUE' => '/shop/cart/success.php'
                ],
                'MERCHANT_ID' => [
                    'KEY' => 'VALUE',
                    'VALUE' => '111111'
                ],
                'SECURITY_KEY' => [
                    'KEY' => 'VALUE',
                    'VALUE' => '11111'
                ],
            ];

            foreach ($arParamsConsumer as $key => $val) {
                \Bitrix\Sale\Internals\BusinessValueTable::add([
                    'CODE_KEY' => $key,
                    'PERSON_TYPE_ID' => 0,
                    'PROVIDER_KEY' => $val['KEY'],
                    'PROVIDER_VALUE' => $val['VALUE'],
                    'CONSUMER_KEY' => 'PAYSYSTEM_' . $paySystemId
                ]);
            }
        }
    }

    public function UnInstallDb()
    {
        $paySystemId = Option::get($this->MODULE_ID, self::OPTION_PAYSYS_ID);
        if ($paySystemId) {
            \Bitrix\Sale\PaySystem\Manager::delete($paySystemId);
        }
    }


    public function InstallFiles($arParams = array())
    {
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/sale_payment/', $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/sale_payment', true, true);
        return true;
    }


    public function UnInstallFiles()
    {
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/sale_payment/', $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/sale_payment');
        return true;
    }

}