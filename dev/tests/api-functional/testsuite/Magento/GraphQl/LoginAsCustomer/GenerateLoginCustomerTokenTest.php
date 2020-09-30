<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\LoginAsCustomer;

use Exception;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\AdminTokenServiceInterface as AdminTokenService;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * API-functional tests cases for generateCustomerToken mutation
 */
class GenerateLoginCustomerTokenTest extends GraphQlAbstract
{

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var AdminTokenService
     */
    private $adminTokenService;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->adminTokenService = $objectManager->get(AdminTokenService::class);
    }

    /**
     * Verify with Admin email ID and Magento_LoginAsCustomer::login is enabled
     *
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/admin.php
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/login_as_customer_config_enable.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @throws Exception
     */
    public function testGenerateCustomerValidTokenLoginAsCustomerEnabled()
    {
        $customerEmail = 'customer@example.com';

        $mutation = $this->getQuery($customerEmail);

        $response = $this->graphQlMutation(
            $mutation,
            [],
            '',
            $this->getAdminHeaderAuthentication('TestAdmin1', 'Zilker777')
        );
        $this->assertArrayHasKey('generateCustomerTokenAsAdmin', $response);
        $this->assertIsArray($response['generateCustomerTokenAsAdmin']);
    }

    /**
     * Verify with Admin email ID and Magento_LoginAsCustomer::login is disabled
     *
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/admin.php
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/login_as_customer_config_disable.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @throws Exception
     */
    public function testGenerateCustomerValidTokenLoginAsCustomerDisabled()
    {
        $customerEmail = 'customer@example.com';

        $mutation = $this->getQuery($customerEmail);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Login as Customer is disabled..");

        $response = $this->graphQlMutation(
            $mutation,
            [],
            '',
            $this->getAdminHeaderAuthentication('TestAdmin1', 'Zilker777')
        );
        $this->assertArrayHasKey('generateCustomerTokenAsAdmin', $response);
        $this->assertIsArray($response['generateCustomerTokenAsAdmin']);
    }

    /**
     * Verify with Customer Token in auth header
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/login_as_customer_config_enable.php
     * @throws Exception
     */
    public function testGenerateCustomerTokenLoginWithCustomerCredentials()
    {
        $customerEmail = 'customer@example.com';
        $password = 'password';

        $mutation = $this->getQuery($customerEmail);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The current customer isn't authorized.");

        $response = $this->graphQlMutation(
            $mutation,
            [],
            '',
            $this->getCustomerHeaderAuthentication($customerEmail, $password)
        );
        $this->assertArrayHasKey('generateCustomerTokenAsAdmin', $response);
        $this->assertIsArray($response['generateCustomerTokenAsAdmin']);
    }

    /**
     * Test with invalid data.
     *
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/admin.php
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/login_as_customer_config_enable.php
     *
     * @dataProvider dataProviderInvalidInfo
     * @param string $adminUserName
     * @param string $adminPassword
     * @param string $customerEmail
     * @param string $message
     */
    public function testGenerateCustomerTokenInvalidData(
        string $adminUserName,
        string $adminPassword,
        string $customerEmail,
        string $message
    ) {
        $this->expectException(Exception::class);

        $mutation = $this->getQuery($customerEmail);
        $this->expectExceptionMessage($message);
        $response = $this->graphQlMutation(
            $mutation,
            [],
            '',
            $this->getAdminHeaderAuthentication($adminUserName, $adminPassword)
        );
    }

    /**
     * Provides invalid test cases data
     *
     * @return array
     */
    public function dataProviderInvalidInfo(): array
    {
        return [
            'invalid_admin_user_name' => [
                'TestAdmin(^%',
                'Zilker777',
                'customer@example.com',
                'The account sign-in was incorrect or your account is disabled temporarily. ' .
                'Please wait and try again later.'
            ],
            'invalid_admin_password' => [
                'TestAdmin1',
                'invalid_password',
                'customer@example.com',
                'The account sign-in was incorrect or your account is disabled temporarily. ' .
                'Please wait and try again later.'
            ]
        ];
    }

    /**
     * @param string $customerEmail
     * @return string
     */
    private function getQuery(string $customerEmail) : string
    {
        return <<<MUTATION
mutation{
  generateCustomerTokenAsAdmin(input: {
    customer_email: "{$customerEmail}"
  }){
    customer_token
  }
}
MUTATION;
    }

    /**
     * Generate customer authentication token
     *
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/admin.php
     *
     * @param string $username
     * @param string $password
     * @return string[]
     * @throws AuthenticationException
     */
    public function getCustomerHeaderAuthentication(
        string $username = 'github@gmail.com',
        string $password = 'Zilker777'
    ): array {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);

        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    /**
     * To get admin access token
     *
     * @param string $userName
     * @param string $password
     * @return string[]
     * @throws AuthenticationException
     */
    private function getAdminHeaderAuthentication(string $userName, string $password)
    {
        try {
            $adminAccessToken = $this->adminTokenService->createAdminAccessToken($userName, $password);

            return ['Authorization' => 'Bearer ' . $adminAccessToken];
        } catch (\Exception $e) {
            throw new AuthenticationException(
                __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                )
            );
        }
    }
}
