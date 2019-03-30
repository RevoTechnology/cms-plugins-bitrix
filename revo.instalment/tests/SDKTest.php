<?php

class SDKTest extends PHPUnit\Framework\TestCase
{
    public function testSDK()
    {
        \Bitrix\Main\Loader::includeModule('revo.instalment');

        $config = new Revo\Sdk\Config(
            [
                'testMode' => true,
                'redirectUrl' => 'http://example.com/',
                'callbackUrl' => 'http://example.com/',
                'storeId' => 1,
                'secret' => 'secret'
            ]
        );

        try {
            $client = new Revo\Sdk\API($config);
            $response = $client->preorderIframeLink();
        } catch (\Revo\Sdk\Error $error) {
            $this->fail('API Test error: ' . $error->getMessage());
            $response = '';
        }

        $this->assertStringContainsString('http:', $response, 'Не удалось получить ссылку для iframe');
    }
}
