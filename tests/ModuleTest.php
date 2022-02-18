<?php

use Revo\Helpers\Extensions;

class ModuleTest extends PHPUnit\Framework\TestCase
{
    public function testInstall() {
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        global $USER;
        $USER->Authorize(1);

        $MODULE_PATH = realpath(dirname(__FILE__) . '/../');
        include $MODULE_PATH . '/install/index.php';
        $class = str_replace('.', '_', $moduleID);
        $module = new $class();
        if (\Bitrix\Main\Loader::includeModule($moduleID)) {
            $module->DoUnInstall();
        }
        $module->DoInstall();

        $this->assertTrue(\Bitrix\Main\Loader::includeModule($moduleID), '�� ������� ���������� ������');

    }

    public function testDeclined() {

    }
}
