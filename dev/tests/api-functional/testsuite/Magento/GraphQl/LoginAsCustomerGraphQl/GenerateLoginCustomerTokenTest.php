<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\LoginAsCustomerGraphQl;

use Exception;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\AdminTokenServiceInterface as AdminTokenService;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * API-functional tests cases for generateCustomerToken mutation
 * @SuppressWarnings(PHPMD)
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
     * @magentoConfigFixture admin_store login_as_customer/general/enabled 1
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/customer.php
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
            $this->getAdminHeaderAuthentication('TestAdmin1', \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD)
        );
        $this->assertArrayHasKey('generateCustomerTokenAsAdmin', $response);
        $this->assertIsArray($response['generateCustomerTokenAsAdmin']);
    }

    /**
     * Verify with Admin email ID and Magento_LoginAsCustomer::login is disabled
     *
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/admin.php
     * @magentoConfigFixture admin_store login_as_customer/general/enabled 0
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/customer.php
     * @throws Exception
     */
    public function testGenerateCustomerValidTokenLoginAsCustomerDisabled()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Login as Customer is disabled.");

        $customerEmail = 'customer@example.com';

        $mutation = $this->getQuery($customerEmail);
        $response = $this->graphQlMutation(
            $mutation,
            [],
            '',
            $this->getAdminHeaderAuthentication('TestAdmin1', \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD)
        );
    }

    /**
     * Verify with Customer Token in auth header
     *
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/customer.php
     * @magentoConfigFixture admin_store login_as_customer/general/enabled 1
     * @throws Exception
     */
    public function testGenerateCustomerTokenLoginWithCustomerCredentials()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The current customer isn't authorized.");

        $customerEmail = 'customer@example.com';
        $password = 'password';

        $mutation = $this->getQuery($customerEmail);

        $this->graphQlMutation(
            $mutation,
            [],
            '',
            $this->getCustomerHeaderAuthentication($customerEmail, $password)
        );
    }

    /**
     * Test with invalid data.
     *
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/admin.php
     * @magentoConfigFixture admin_store login_as_customer/general/enabled 1
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
        $this->expectExceptionMessage($message);

        $mutation = $this->getQuery($customerEmail);
        $this->graphQlMutation(
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
                \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
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
        string $password = \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
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
