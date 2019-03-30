<?php

class ModuleTest extends PHPUnit\Framework\TestCase
{
    const MODULE_ID = 'revo.instalment';
    public function testInstall() {
        global $USER;
        $USER->Authorize(1);

        $MODULE_PATH = realpath(dirname(__FILE__) . '/../');
        include $MODULE_PATH . '/install/index.php';
        $class = str_replace('.', '_', self::MODULE_ID);
        $module = new $class();
        if (\Bitrix\Main\Loader::includeModule(self::MODULE_ID)) {
            $module->DoUnInstall();
        }
        $module->DoInstall();

        $this->assertTrue(\Bitrix\Main\Loader::includeModule(self::MODULE_ID), 'Не удалось установить модуль');
    }
}
