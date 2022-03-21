<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\LoginAsCustomerGraphQl;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
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
     * Verify customer token as admin with store
     *
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/admin.php
     * @magentoConfigFixture admin_store login_as_customer/general/enabled 1
     * @magentoApiDataFixture Magento/LoginAsCustomer/_files/customer_with_second_store.php.php
     * @throws Exception
     */
    public function testGenerateCustomerValidTokenAsAdminWithStore()
    {
        $customerEmail = '2ndstorecustomer@example.com';

        $mutation = $this->getQuery($customerEmail);

        $customerRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            CustomerRepositoryInterface::class
        );
        $customer = $customerRepository->get($customerEmail);
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Store\Model\Store::class);
        $store->load($customer->getStoreId());
        echo $customer->getWebsiteId()."<=====>".$customer->getStoreId()."<===>".$store->getCode();

        $header = $this->getAdminHeaderAuthenticationWithStore(
            'TestAdmin1',
            \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
            'fixture_second_store'
        );echo $header['Authorization'];

        $response = $this->graphQlMutation(
            $mutation,
            [],
            '',
            $header
        );var_dump($response);
        $this->assertArrayHasKey('generateCustomerTokenAsAdmin', $response);
        $this->assertIsArray($response['generateCustomerTokenAsAdmin']);
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
     * To get admin access token with store
     *
     * @param string $userName
     * @param string $password
     * @param string $storeCode
     * @return string[]
     * @throws AuthenticationException
     */
    private function getAdminHeaderAuthenticationWithStore(string $userName, string $password, string $storeCode)
    {
        try {
            $adminAccessToken = $this->adminTokenService->createAdminAccessToken($userName, $password);
            return ['Authorization' => 'Bearer ' . $adminAccessToken, 'store' => $storeCode];
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
