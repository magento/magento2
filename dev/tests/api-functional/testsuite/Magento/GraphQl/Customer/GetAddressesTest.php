<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * Test for customer address retrieval.
 */
class GetAddressesTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var LockCustomer
     */
    private $lockCustomer;

    protected function setUp()
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->lockCustomer = Bootstrap::getObjectManager()->get(LockCustomer::class);
    }
    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testGetCustomerWithAddresses()
    {
        $query = $this->getQuery();

        $userName = 'customer@example.com';
        $password = 'password';

        $customerToken = $this->customerTokenService->createCustomerAccessToken($userName, $password);
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
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @expectedException Exception
     * @expectedExceptionMessage GraphQL response contains errors: The account is locked.
     */
    public function testGetCustomerAddressIfAccountIsLocked()
    {
        $query = $this->getQuery();

        $userName = 'customer@example.com';
        $password = 'password';
        $this->lockCustomer->execute(1);

        $customerToken = $this->customerTokenService->createCustomerAccessToken($userName, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];

        $this->graphQlQuery($query, [], '', $headerMap);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @expectedException Exception
     * @expectedExceptionMessage GraphQL response contains errors: The current customer isn't authorized.
     */
    public function testGetCustomerAddressIfUserIsNotAuthorized()
    {
        $query = $this->getQuery();

        $this->graphQlQuery($query);
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

    /**
     * @return string
     */
    private function getQuery(): string
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
        return $query;
    }
}
