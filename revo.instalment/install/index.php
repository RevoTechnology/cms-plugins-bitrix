<?
use Bitrix\Main\Localization\Loc,
    \Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class revo_instalment extends CModule
{

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
        $this->MODULE_NAME = GetMessage('LK_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('LK_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = GetMessage('LK_MODULE_PARTNER_NAME');
    }


    public function DoInstall()
    {
        global $USER;
        if ($USER->IsAdmin()) {
            RegisterModule($this->MODULE_ID);
            $this->InstallFiles();
            $this->InstallEvents();

            $GLOBALS['errors'] = $this->errors;
        }
    }


    public function DoUninstall()
    {
        global $USER;
        if ($USER->IsAdmin()) {
            UnRegisterModule($this->MODULE_ID);
            $this->UnInstallFiles();
            $this->UnInstallEvents();
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


    public function InstallFiles($arParams = array())
    {
		CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/local/modules/'.$this->MODULE_ID.'/install/admin/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin');
        return true;
    }


    public function UnInstallFiles()
    {
		DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/local/modules/'.$this->MODULE_ID.'/install/admin/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin');
        return true;
    }

}