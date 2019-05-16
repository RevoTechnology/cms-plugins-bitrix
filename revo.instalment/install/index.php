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
                GetMessage('REVO_MODULE_SALE_FAIL')
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
        RegisterModuleDependences('main', 'onProlog',$this->MODULE_ID, '\Revo\Events','onProlog');
        RegisterModuleDependences('sale', 'OnSaleStatusOrder',$this->MODULE_ID, '\Revo\Events','onStatusOrder');
    }

    public function UnInstallEvents()
    {
        UnRegisterModuleDependences('main', 'onProlog',$this->MODULE_ID, '\Revo\Events','onProlog');
        UnRegisterModuleDependences('sale', 'OnSaleStatusOrder',$this->MODULE_ID, '\Revo\Events','onStatusOrder');
    }

    public function InstallDb()
    {
        require_once __DIR__ . './../classes/models/DataManager.php';
        require_once __DIR__ . './../classes/models/RegisteredUsersTable.php';

        \Bitrix\Main\Loader::includeModule('sale');
        $arAdd = array(
            'NAME' => GetMessage('REVO_MODULE_PAYMENT_NAME'),
            'PSA_NAME' => GetMessage('REVO_MODULE_PAYMENT_NAME'),
            'ACTIVE' => 'Y',
            'CAN_PRINT_CHECK' => 'N',
            'CODE' => '',
            'NEW_WINDOW' => 'N',
            'ALLOW_EDIT_PAYMENT' => 'Y',
            'IS_CASH' => 'N',
            'SORT' => 100,
            'ENCODING' => 'utf-8',
            'DESCRIPTION' => GetMessage('REVO_MODULE_PAYMENT_DESC'),
            'ACTION_FILE' => '/local/php_interface/include/sale_payment/revo',
            'PS_MODE' => '',
            'AUTO_CHANGE_1C' => 'N',
            'HAVE_PAYMENT' => 'Y'
        );
        if (array_key_exists('ENTITY_REGISTRY_TYPE', \Bitrix\Sale\Internals\PaySystemActionTable::getMap())) {
            $arAdd['ENTITY_REGISTRY_TYPE'] = \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER;
        }

        $result = \Bitrix\Sale\Internals\PaySystemActionTable::add($arAdd);


        if (!$result->isSuccess()) {
            $this->errors[] = GetMessage('REVO_MODULE_PAYMENT_FAIL');
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

            $fields = array(
                "SERVICE_ID" => $paySystemId,
                "SERVICE_TYPE" => Bitrix\Sale\Services\PaySystem\Restrictions\Manager::SERVICE_TYPE_PAYMENT,
                "SORT" => 100,
                "PARAMS" => [
                    'MIN_VALUE' => 3000
                ]
            );

            Bitrix\Sale\Services\PaySystem\Restrictions\Price::save($fields);
        }


        $prefix = CMain::IsHTTPS() ? 'https' : 'http' . '://' ;
            Option::set(
            $this->MODULE_ID,
            'callback_url',
                $prefix . $_SERVER['HTTP_HOST'] . '/ajax/revo.instalment/ajax.php'
        );

        Option::set(
            $this->MODULE_ID,
            'redirect_url',
            $prefix . $_SERVER['HTTP_HOST'] . '/personal/cart/'
        );

        \Revo\Models\RegisteredUsersTable::reinstallTable();
    }

    public function UnInstallDb()
    {
        $paySystemId = Option::get($this->MODULE_ID, self::OPTION_PAYSYS_ID);
        if ($paySystemId) {
            \Bitrix\Main\Loader::includeModule('sale');
            \Bitrix\Sale\PaySystem\Manager::delete($paySystemId);
        }

        \Revo\Models\RegisteredUsersTable::dropTableIfExist();
    }


    public function InstallFiles($arParams = array())
    {
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/sale_payment/', $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/sale_payment', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/ajax/', $_SERVER['DOCUMENT_ROOT'] . '/ajax/'.$this->MODULE_ID.'/', true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/components/', $_SERVER['DOCUMENT_ROOT'] . '/local/components/revo/', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/snippets/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/templates/.default/snippets/', true, true);
        mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/check/');
        return true;
    }


    public function UnInstallFiles()
    {
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/sale_payment/', $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/sale_payment');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/ajax/', $_SERVER['DOCUMENT_ROOT'] . '/ajax/'.$this->MODULE_ID.'/');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/components/', $_SERVER['DOCUMENT_ROOT'] . '/local/components/revo/');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/snippets/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/templates/.default/snippets/');
        rmdir($_SERVER['DOCUMENT_ROOT'] . '/upload/check/');
        return true;
    }

}