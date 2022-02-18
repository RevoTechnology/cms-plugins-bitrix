<?php

use Revo\Helpers\Extensions;

class DocumentsTest extends PHPUnit\Framework\TestCase
{
    const FILE_TEST_PATH = '/upload/testpdf.pdf';

    public function testConvertToPDF() {
        $extension = new Extensions();
        $moduleID = $extension->getModuleID();
        if (!\Bitrix\Main\Loader::includeModule($moduleID)) {
            $this->fail('Module not installed');
        }
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . self::FILE_TEST_PATH;

        \Revo\Documents::billToPDF(
            455,
            $fullPath
        );

        $this->assertTrue(
            file_exists($fullPath),
            'Pdf was not created'
        );

        unlink($fullPath);


    }
}
