<?php
namespace Revo\Helpers;

class Extensions {

    public function getModuleID()
    {
        $config = require $_SERVER['DOCUMENT_ROOT'] . '/local/components/revo/config/config.php';
        return $config['moduleID'];
    }
}