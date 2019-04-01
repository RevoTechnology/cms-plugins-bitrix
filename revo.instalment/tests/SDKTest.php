<?php

class SDKTest extends PHPUnit\Framework\TestCase
{
    public function testSDK()
    {
        if (!\Bitrix\Main\Loader::includeModule('revo.instalment')) {
            $this->fail('Module not installed');
        }

        $config = new Revo\Sdk\Config(
            [
                'testMode' => true,
                'redirectUrl' => 'http://example.com/',
                'callbackUrl' => 'http://example.com/',
                'storeId' => 204,
                'secret' => '6279a164f5cb8bbe93f7'
            ]
        );

        try {
            $client = new Revo\Sdk\API($config);
            $response = $client->preorderIframeLink();
        } catch (\Revo\Sdk\Error $error) {
            $this->fail('API Test error: ' . $error->getMessage());
            $response = '';
        }

        $this->assertStringContainsString('http', $response, 'Can\'t get link for iframe');
    }

    public function testInstalment() {
        if (!\Bitrix\Main\Loader::includeModule('revo.instalment')) {
            $this->fail('Module not installed');
        }

        $el = \Revo\Instalment::getInstance();
        $this->assertStringContainsString('http', $el->getIframeUri(), 'Can\'t get link for iframe');
    }
}
