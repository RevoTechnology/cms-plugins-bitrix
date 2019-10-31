<?

use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Config\Option;
use Bitrix\Main\Text\Encoding;

Loc::loadMessages(__FILE__);

class a_revo extends CModule
{

    const OPTION_PAYSYS_ID = 'paysys_id';

    public $MODULE_ID = 'a.revo';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI = 'https://revo.ru/';


    public function __construct()
    {
        $this->errors = false;
        $arModuleVersion = array();
        $path = str_replace('\\', '/', __FILE__);
        $path = substr($path, 0, strlen($path) - strlen('/index.php'));
        include($path . '/version.php');
        $this->MODULE_ID = 'a.revo';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = GetMessage('REVO_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('REVO_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = GetMessage('REVO_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = 'https://revo.ru/';
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
        RegisterModuleDependences('main', 'onProlog', $this->MODULE_ID, '\Revo\Events','onProlog');
        RegisterModuleDependences('sale', 'OnSaleStatusOrder', $this->MODULE_ID, '\Revo\Events','onStatusOrder');
        RegisterModuleDependences('sale', 'OnSaleCancelOrder', $this->MODULE_ID, '\Revo\Events','onCancelOrder');
        RegisterModuleDependences('sale', 'OnBeforeOrderUpdate', $this->MODULE_ID, '\Revo\Events','onUpdateOrder');
        $eventManager = Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandler('sale', 'OnPaymentPaid', $this->MODULE_ID, '\Revo\Events','onSalePaymentPaid');
    }

    public function UnInstallEvents()
    {
        UnRegisterModuleDependences('main', 'onProlog', $this->MODULE_ID, '\Revo\Events','onProlog');
        UnRegisterModuleDependences('sale', 'OnSaleStatusOrder', $this->MODULE_ID, '\Revo\Events','onStatusOrder');
        UnRegisterModuleDependences('sale', 'OnSaleCancelOrder', $this->MODULE_ID, '\Revo\Events','onCancelOrder');
        UnRegisterModuleDependences('sale', 'OnBeforeOrderUpdate', $this->MODULE_ID, '\Revo\Events','onUpdateOrder');
        $eventManager = Bitrix\Main\EventManager::getInstance();
        $eventManager->unRegisterEventHandler('sale', 'OnPaymentPaid', $this->MODULE_ID, '\Revo\Events','onSalePaymentPaid');
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
            'LOGOTIP' => CFile::SaveFile(CFile::MakeFileArray(
                dirname(__FILE__) . '/img/logo.png'
             ), '/a.revo/'),
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


        $prefix = (CMain::IsHTTPS() ? 'https' : 'http') . '://' ;
            Option::set(
            $this->MODULE_ID,
            'callback_url',
                $prefix . $_SERVER['HTTP_HOST'] . '/ajax/a.revo/ajax.php'
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
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/sale_payment/', $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/sale_payment', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/ajax/', $_SERVER['DOCUMENT_ROOT'] . '/ajax/'.$this->MODULE_ID.'/', true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/components/', $_SERVER['DOCUMENT_ROOT'] . '/local/components/revo/', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/snippets/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/templates/.default/snippets/', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/css/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/css/'.$this->MODULE_ID.'/', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/js/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/'.$this->MODULE_ID.'/', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/html/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/html/'.$this->MODULE_ID.'/', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/bitrix/snippets/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/templates/.default/snippets/', true, true);

        $snippetsExists = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/templates/.default/snippets/.content.php');
        if ($snippetsExists) {
            $snippetsAdd = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/snippet_content/.content.add.php');
        } else {
            $snippetsAdd = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/snippet_content/.content.php');
        }
        if (Application::getInstance()->isUtfMode()) {
            $snippetsAdd = \Bitrix\Main\Text\Encoding::convertEncoding(
                $snippetsAdd,
                'cp1251',
                'utf-8'
            );
        }
        $snippetsExists = $snippetsExists.$snippetsAdd;
        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/templates/.default/snippets/.content.php',
            $snippetsExists
        );


        if (Application::getInstance()->isUtfMode()) {
            $textSnippet = Encoding::convertEncoding(
                file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/bitrix/templates/.default/snippets/revo/fast-buy.snp'),
                'cp1251',
                'utf-8'
            );

            file_put_contents(
                $_SERVER['DOCUMENT_ROOT'] . '/bitrix/templates/.default/snippets/revo/fast-buy.snp',
                $textSnippet
            );
        }
        mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/check/');
        return true;
    }


    public function UnInstallFiles()
    {
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/sale_payment/', $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/sale_payment');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/ajax/', $_SERVER['DOCUMENT_ROOT'] . '/ajax/'.$this->MODULE_ID.'/');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/components/', $_SERVER['DOCUMENT_ROOT'] . '/local/components/revo/');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/snippets/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/templates/.default/snippets/');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/css/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/css/'.$this->MODULE_ID.'/');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/js/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/'.$this->MODULE_ID.'/');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/html/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/html/'.$this->MODULE_ID.'/');
        rmdir($_SERVER['DOCUMENT_ROOT'] . '/upload/check/');
        return true;
    }

}