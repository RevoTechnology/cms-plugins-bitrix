<?php

if(!function_exists('loadRevoClasses')){
    /**
     * Function recursively parse directory and associate files with classes.
     * File name have to match with class name and class should have namespace $rootNameSpace + \ + SUBDIRECTORY_NAME
     *
     * @param $dir string - classes directory of some module
     * @param $rootNameSpace string - Namespace prefix of all classes
     * @param $rootModuleDir string - Dir of module
     * @return array - Array for \Bitrix\Main\Loader::registerAutoLoadClasses second parameter
     * @author emaslov
     */
    function loadRevoClasses($dir, $rootNameSpace, $rootModuleDir) {
        $arClasses = array();

        $classDirIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($classDirIterator as $filePath => $fileObj) {
            if (in_array($filePath, array('..', '.'))) continue;
            if (is_dir($filePath)) continue;

            $splitDir = explode(DIRECTORY_SEPARATOR, $filePath);
            $fileName = array_pop($splitDir);
            $dirName = array_pop($splitDir);

            $className = explode('.', $fileName);
            $className = array_shift($className);

            $arClasses[
            $rootNameSpace . '\\' .
            (!in_array($dirName, ['classes', 'lib']) ? ucfirst($dirName) . '\\' : '') .
            ucfirst($className)
            ] = str_replace($rootModuleDir . DIRECTORY_SEPARATOR, '', $filePath);

        }

        return $arClasses;
    }
}


$rootNameSpace = 'Revo';
$classesDirPath = __DIR__ . DIRECTORY_SEPARATOR . 'classes';

\Bitrix\Main\Loader::registerAutoLoadClasses(
    'revo.instalment',
    loadRevoClasses(
        $classesDirPath,
        $rootNameSpace,
        __DIR__
    )
);

\CJSCore::RegisterExt('revo.instalment', array(
    'lang' => '/local/modules/revo.instalment/lang/'.LANGUAGE_ID.'/js.php'
));

require_once __DIR__ . '/vendor/autoload.php';