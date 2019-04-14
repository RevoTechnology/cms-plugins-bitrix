<?php

class DocumentsTest extends PHPUnit\Framework\TestCase
{
    const FILE_TEST_PATH = '/upload/testpdf.pdf';

    public function testConvertToPDF() {
        if (!\Bitrix\Main\Loader::includeModule('revo.instalment')) {
            $this->fail('Module not installed');
        }
        $html = \Revo\Documents::printCheck(455);
        \Revo\Documents::convertHtmlToPdf(
            \Bitrix\Main\Text\Encoding::convertEncoding(
                $html, SITE_CHARSET, 'cp1251'
            ),
            self::FILE_TEST_PATH
        );
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . self::FILE_TEST_PATH;

        $this->assertTrue(
            file_exists($fullPath),
            'Pdf was not created'
        );

//        unlink($fullPath);


    }
}
