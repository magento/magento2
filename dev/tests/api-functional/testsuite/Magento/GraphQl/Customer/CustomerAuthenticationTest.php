<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use \Magento\Integration\Api\CustomerTokenServiceInterface;

class CustomerAuthenticationTest extends GraphQlAbstract
{
    /**
     * Verify customers with valid credentials with a customer bearer token
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */

    public function testRegisteredCustomerWithValidCredentials()
    {
        $query
            = <<<QUERY
{
  customer 
  {
    id
    website_id
    store_id
    lastname
    default_billing
    default_shipping    
   }
}
QUERY;

        $userName = 'customer@example.com';
        $password = 'password';
        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = ObjectManager::getInstance()
                               ->get(\Magento\Integration\Api\CustomerTokenServiceInterface::class);
        $customerToken = $customerTokenService->createCustomerAccessToken($userName, $password);
        $this->setToken($customerToken);

        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = ObjectManager::getInstance()->get(CustomerRepositoryInterface::class);
        $customer = $customerRepository->get('customer@example.com');
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('customer', $response);
        $this->assertCustomerFields($customer, $response['customer']);
    }

    /**
     * Verify Customer with valid token but invalid login credentials
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCustomerTokenWithInvalidCredentials()
    {
        $userName = 'customer1@example.com';
        $password = 'wrongPassword';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You did not sign in correctly or your account is temporarily disabled.');

       /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = ObjectManager::getInstance()
                                ->get(\Magento\Integration\Api\CustomerTokenServiceInterface::class);
        $customerToken = $customerTokenService->createCustomerAccessToken($userName, $password);
        $this->setToken($customerToken);
    }

    /**
     * Verify customer with valid credentials but without the bearer token
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */

    public function testCustomerWithValidCredentialsWithoutToken()
    {
         $query
           = <<<QUERY
{
  customer 
  {
    id
    website_id
    store_id
    lastname
    default_billing
    default_shipping    
   }
}
QUERY;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: Current customer' . ' ' .
           'does not have access to the resource "customer"');
        $this->graphQlQuery($query);
    }

    /**
     * @param CustomerInterface $customer
     * @param  $actualResponse
     */

    public function assertCustomerFields($customer, $actualResponse)
    {
        /**
         * ['customer_object_field_name', 'expected_value']
         */
        $assertionMap = [
            ['response_field' => 'id', 'expected_value' => $customer->getId()],
            ['response_field' => 'website_id', 'expected_value' => $customer->getWebsiteId()],
            ['response_field' => 'store_id', 'expected_value' => $customer->getStoreId()],
            ['response_field' => 'lastname', 'expected_value' => $customer->getLastname()],
            ['response_field' => 'default_shipping', 'expected_value' => $customer->getDefaultShipping()],
            ['response_field' => 'default_billing', 'expected_value' => $customer->getDefaultBilling()]
        ];

        $this->assertResponseFields($actualResponse, $assertionMap);
    }

    /**
     * @param array $actualResponse
     * @param array $assertionMap ['response_field_name' => 'response_field_value', ...]
     *                         OR [['response_field' => $field, 'expected_value' => $value], ...]
     */
    private function assertResponseFields($actualResponse, $assertionMap)
    {
        foreach ($assertionMap as $key => $assertionData) {
            $expectedValue = isset($assertionData['expected_value'])
                ? $assertionData['expected_value']
                : $assertionData;
            $responseField = isset($assertionData['response_field']) ? $assertionData['response_field'] : $key;
            $this->assertNotNull(
                $expectedValue,
                "Value of '{$responseField}' field must not be NULL"
            );
            $this->assertEquals(
                $expectedValue,
                $actualResponse[$responseField],
                "Value of '{$responseField}' field in response does not match expected value: "
                . var_export($expectedValue, true)
            );
        }
    }
}
