<?php
/**
 * Copyright 2025 Adobe
 * All rights reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerApi\Api;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\LoginAsCustomer\Model\GenerateAuthenticationSecret;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\User\Model\User;

class GenerateLoginCustomerTokenTest extends WebapiAbstract
{
    private const RESOURCE_PATH  = '/V1/integration/customer/login-as-customer';
    private const ADMIN_USERNAME = 'TestAdminLoginAsCustomer';

    private ObjectManagerInterface $objectManager;
    private AdminTokenServiceInterface $adminTokens;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->adminTokens   = $this->objectManager->get(AdminTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/customer.php
     * @magentoApiDataFixture Magento/LoginAsCustomerApi/_files/admin_login_as_customer.php
     * @magentoConfigFixture default/login_as_customer/general/enabled 1
     */
    public function testValidSecretGeneratesToken(): void
    {
        $user = $this->loadAdminUser();
        $secret = $this->generateSecret((int)$user->getId());
        $requestData = ['secret' => $secret];

        $token = $this->_webApiCall($this->getServiceInfo(), $requestData);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    /**
     * @magentoApiDataFixture Magento/LoginAsCustomerApi/_files/admin_login_as_customer.php
     * @magentoConfigFixture default/login_as_customer/general/enabled 1
     */
    public function testInvalidSecretThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('{"message":"Invalid or expired secret."}');

        $this->_webApiCall($this->getServiceInfo(), ['secret' => 'invalid-secret']);
    }

    /**
     * @magentoApiDataFixture Magento/LoginAsCustomerApi/_files/admin_login_as_customer.php
     * @magentoConfigFixture default/login_as_customer/general/enabled 0
     */
    public function testModuleDisabledThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('{"message":"Login As Customer module is disabled."}');

        $this->_webApiCall($this->getServiceInfo(), ['secret' => 'test']);
    }

    /**
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/customer.php
     * @magentoApiDataFixture Magento/LoginAsCustomerApi/_files/admin_login_as_customer.php
     * @magentoConfigFixture default/login_as_customer/general/enabled 1
     */
    public function testAdminDeletedAfterSecretGeneration(): void
    {
        $user = $this->loadAdminUser();
        $secret = $this->generateSecret((int)$user->getId());

        // Delete admin user
        $userResource = $this->objectManager->get(\Magento\User\Model\ResourceModel\User::class);
        $userResource->delete($user);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The account sign-in was incorrect or your account is disabled temporarily. Please wait and try again later.');

        $this->_webApiCall($this->getServiceInfo(), ['secret' => $secret]);
    }

    /**
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/customer.php
     * @magentoApiDataFixture Magento/LoginAsCustomerApi/_files/admin_login_as_customer.php
     * @magentoConfigFixture default/login_as_customer/general/enabled 1
     */
    public function testTamperedSecretThrowsException(): void
    {
        $user = $this->loadAdminUser();
        $secret = $this->generateSecret((int)$user->getId());
        $tampered = $secret . 'AA';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('{"message":"Invalid or expired secret."}');
        $this->_webApiCall($this->getServiceInfo(), ['secret' => $tampered]);
    }

    /**
     * Generate REST service info with token.
     */
    private function getServiceInfo(): array
    {
        $token = $this->adminTokens->createAdminAccessToken(
            self::ADMIN_USERNAME,
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        );

        return [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
                'token' => $token,
            ],
        ];
    }

    /**
     * Loads the admin user created by fixture.
     */
    private function loadAdminUser(): User
    {
        $user = $this->objectManager->create(User::class);
        $user->load(self::ADMIN_USERNAME, 'username');

        if (!$user->getId()) {
            $this->fail('Admin user not found. Fixture admin_login_as_customer.php failed.');
        }

        return $user;
    }

    /**
     * Generates secret for given admin.
     */
    private function generateSecret(int $adminId): string
    {
        $authData = $this->objectManager
            ->get(AuthenticationDataInterfaceFactory::class)
            ->create([
                'customerId' => 1,
                'adminId' => $adminId,
            ]);

        return $this->objectManager->get(GenerateAuthenticationSecret::class)
            ->execute($authData);
    }
}
