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

class GetAddressesTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testGetCustomerWithAddresses()
    {
        $query
            = <<<QUERY
{
  customer {
    id
    addresses {
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
        self::assertEquals($customer->getId(), $response['customer']['id']);
        $this->assertCustomerAddressesFields($customer, $response);
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
