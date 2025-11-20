<?php
/**
 * Copyright 2025 Adobe
 * All rights reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * @magentoConfigFixture default/login_as_customer/general/enabled 1
 */
class GenerateLoginCustomerTokenTest extends WebapiAbstract
{
    private const SERVICE_VERSION = 'V1';
    private const SERVICE_NAME = 'generateLoginCustomerTokenV1';
    private const RESOURCE_PATH = '/V1/integration/customer/login-as-customer';

    /**
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/customer.php
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/admin.php
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/login_as_customer_secret.php
     * @magentoConfigFixture admin_store login_as_customer/general/enabled 1
     */
    public function testValidSecretGeneratesToken(): void
    {
        $secret = $GLOBALS['login_as_customer_secret'];
        $requestData = ["secret" => $secret];
        $token = $this->_webApiCall($this->getServiceInfo(), $requestData);
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    /**
     * @magentoConfigFixture admin_store login_as_customer/general/enabled 1
     */
    public function testInvalidSecretThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('{"message":"Invalid or expired secret."}');
        $requestData = ["secret" => 'invalid-secret'];
        $this->_webApiCall($this->getServiceInfo(), $requestData);
    }

    /**
     * @magentoConfigFixture admin_store login_as_customer/general/enabled 0
     */
    public function testModuleDisabledThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('{"message":"Login As Customer module is disabled."}');
        $requestData = ["secret" => 'test'];
        $this->_webApiCall($this->getServiceInfo(), $requestData);
    }

    /**
     * @return array
     */
    private function getServiceInfo(): array
    {
        return  [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ]
        ];
    }
}
