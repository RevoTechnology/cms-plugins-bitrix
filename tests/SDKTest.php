<?php

class SDKTest extends PHPUnit\Framework\TestCase
{
    const USER_SESSID = 'TEST_SESSID';
    public function testSDK()
    {
        if (!\Bitrix\Main\Loader::includeModule('a.revo')) {
            $this->fail('Module not installed');
        }

        $config = new Revo\Sdk\Config(
            [
                'testMode' => true,
                'storeId' => 204,
                'secret' => '6279a164f5cb8bbe93f7'
            ]
        );

        try {
            $client = new Revo\Sdk\API($config);
            $response = $client->registration();
        } catch (\Revo\Sdk\Error $error) {
            $this->fail('API Test error: ' . $error->getMessage());
            $response = '';
        }

        $this->assertStringContainsString('http', $response, 'Can\'t get link for iframe');
    }

    public function testInstalment() {
        if (!\Bitrix\Main\Loader::includeModule('a.revo')) {
            $this->fail('Module not installed');
        }

        $el = \Revo\Instalment::getInstance();
        $this->assertStringContainsString(
            'http',
            $el->getRegistrationUri(),
            'Can\'t get link for iframe'
        );
    }

    public function testUserSave() {
        if (!\Bitrix\Main\Loader::includeModule('a.revo')) {
            $this->fail('Module not installed');
        }

        $addingUser = \Revo\Models\RegisteredUsersTable::addUser(self::USER_SESSID);
        $this->assertTrue($addingUser, 'User have not been added');

        $userExist = \Revo\Models\RegisteredUsersTable::get(self::USER_SESSID);
        $this->assertTrue(!!$userExist, 'User do not exist');
    }

    public function testFinalizeOrder() {
        if (!\Bitrix\Main\Loader::includeModule('a.revo')) {
            $this->fail('Module not installed');
        }

        $revoClient = \Revo\Instalment::getInstance();
        $response = $revoClient->finalizeOrder(25, 30, 'http://www.africau.edu/images/default/sample.pdf');

        $this->assertTrue($response['status'] == 'ok', 'Finalize order request did not sent');
    }

    public function testOrderData() {
        if (!\Bitrix\Main\Loader::includeModule('a.revo')) {
            $this->fail('Module not installed');
        }

        $orderData = \Revo\Dto\OrderData::getFromGlobalParams([
            'ORDER' => ['ID' => 4],
            'PAYMENT' => ['SUM' => 300]
        ]);

        $this->assertEquals($orderData->order_id,4);
        $this->assertEquals($orderData->amount,300);

        \Bitrix\Main\Config\Option::set('a.revo', 'detail_max_order_part', 50);

        $orderData = \Revo\Dto\OrderData::getFromGlobalParams([
            'ORDER' => ['ID' => 4],
            'PAYMENT' => ['SUM' => 300]
        ]);

        $this->assertEquals($orderData->amount,150);

        \Bitrix\Main\Config\Option::set('a.revo', 'detail_max_order_part', 100);
    }
}
