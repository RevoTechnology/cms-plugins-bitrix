<?php

class SDKTest extends PHPUnit\Framework\TestCase
{
    const USER_SESSID = 'TEST_SESSID';
    public function testSDK()
    {
        if (!\Bitrix\Main\Loader::includeModule('revo.instalment')) {
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
        if (!\Bitrix\Main\Loader::includeModule('revo.instalment')) {
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
        $addingUser = \Revo\Models\RegisteredUsersTable::addUser(self::USER_SESSID);
        $this->assertTrue($addingUser, 'User have not been added');

        $userExist = \Revo\Models\RegisteredUsersTable::get(self::USER_SESSID);
        $this->assertTrue(!!$userExist, 'User do not exist');
    }

    public function finalizeOrderTest() {
        $revoClient = \Revo\Instalment::getInstance();
        $response = $revoClient->finalizeOrder(5, $_SERVER['DOCUMENT_ROOT'] . '/include/logo.png');
        var_dump($response);
    }
}
