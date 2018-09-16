<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Integration\Api\CustomerTokenServiceInterface;

class CustomerAuthenticationTest extends GraphQlAbstract
{
    /**
     * Verify customers with valid credentials with a customer bearer token
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testRegisteredCustomerWithValidCredentials()
    {
        $query
            = <<<QUERY
{
  customer 
  {
    created_at
    group_id
    prefix
    firstname
    middlename
    lastname
    suffix
    email
    default_billing
    default_shipping
    id
      addresses{
      id
      customer_id
      region_id
      country_id
      telephone
      postcode
      city      
      firstname
      lastname
    }
   }
}
QUERY;

        $userName = 'customer@example.com';
        $password = 'password';
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = ObjectManager::getInstance()
                               ->get(\Magento\Integration\Api\CustomerTokenServiceInterface::class);
        $customerToken = $customerTokenService->createCustomerAccessToken($userName, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = ObjectManager::getInstance()->get(CustomerRepositoryInterface::class);
        $customer = $customerRepository->get('customer@example.com');

        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertArrayHasKey('customer', $response);
        $this->assertArrayHasKey('addresses', $response['customer']);
        $this->assertTrue(
            is_array([$response['customer']['addresses']]),
            " Addresses field must be of an array type."
        );
        $this->assertCustomerFields($customer, $response['customer']);
        $this->assertCustomerAddressesFields($customer, $response);
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
    created_at
    group_id
    prefix
    firstname
    middlename
    lastname
    suffix
    email
    default_billing
    default_shipping
    id   
   }
}
QUERY;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: Current customer' . ' ' .
           'does not have access to the resource "customer"');
        $this->graphQlQuery($query);
    }

    /**
     * Verify the all the whitelisted fields for a Customer Object
     *
     * @param CustomerInterface $customer
     * @param $actualResponse
     */
    public function assertCustomerFields($customer, $actualResponse)
    {
        // ['customer_object_field_name', 'expected_value']
        $assertionMap = [
            ['response_field' => 'id', 'expected_value' => $customer->getId()],
            ['response_field' => 'created_at', 'expected_value' => $customer->getCreatedAt()],
            ['response_field' => 'group_id', 'expected_value' => $customer->getGroupId()],
            ['response_field' => 'prefix', 'expected_value' => $customer->getPrefix()],
            ['response_field' => 'firstname', 'expected_value' => $customer->getFirstname()],
            ['response_field' => 'middlename', 'expected_value' => $customer->getMiddlename()],
            ['response_field' => 'lastname', 'expected_value' => $customer->getLastname()],
            ['response_field' => 'suffix', 'expected_value' => $customer->getSuffix()],
            ['response_field' => 'email', 'expected_value' => $customer->getEmail()],
            ['response_field' => 'default_shipping', 'expected_value' => (bool)$customer->getDefaultShipping()],
            ['response_field' => 'default_billing', 'expected_value' => (bool)$customer->getDefaultBilling()],
            ['response_field' => 'id', 'expected_value' => $customer->getId()]
        ];

        $this->assertResponseFields($actualResponse, $assertionMap);
    }

    /**
     * Verify the fields for CustomerAddress object
     *
     * @param CustomerInterface $customer
     * @param array $actualResponse
     */
    public function assertCustomerAddressesFields($customer, $actualResponse)
    {
        /** @var AddressInterface $addresses */
        $addresses = $customer->getAddresses();
        foreach ($addresses as $addressKey => $addressValue) {
            $this->assertNotEmpty($addressValue);
            $assertionMap = [
                ['response_field' => 'id', 'expected_value' => $addresses[$addressKey]->getId()],
                ['response_field' => 'customer_id', 'expected_value' => $addresses[$addressKey]->getCustomerId()],
                ['response_field' => 'region_id', 'expected_value' => $addresses[$addressKey]->getRegionId()],
                ['response_field' => 'country_id', 'expected_value' => $addresses[$addressKey]->getCountryId()],
                ['response_field' => 'telephone', 'expected_value' => $addresses[$addressKey]->getTelephone()],
                ['response_field' => 'postcode', 'expected_value' => $addresses[$addressKey]->getPostcode()],
                ['response_field' => 'city', 'expected_value' => $addresses[$addressKey]->getCity()],
                ['response_field' => 'firstname', 'expected_value' => $addresses[$addressKey]->getFirstname()],
                ['response_field' => 'lastname', 'expected_value' => $addresses[$addressKey]->getLastname()]
            ];
            $this->assertResponseFields($actualResponse['customer']['addresses'][$addressKey], $assertionMap);
        }
    }
}
