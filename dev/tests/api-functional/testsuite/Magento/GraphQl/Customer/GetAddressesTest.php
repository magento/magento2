<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Customer\Test\Fixture\CustomerWithAddresses;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
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

    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->lockCustomer = Bootstrap::getObjectManager()->get(LockCustomer::class);
    }
    #[
        DataFixture(CustomerWithAddresses::class, as: 'customer')
    ]
    public function testGetCustomerWithAddresses(): void
    {
        $customerFixture = DataFixtureStorageManager::getStorage()->get('customer');
        $response = $this->graphQlQuery(
            $this->getCustomerQuery(),
            [],
            '',
            $this->getCustomerAuthHeaders($customerFixture->getEmail())
        );

        $this->assertArrayHasKey('customer', $response);
        $this->assertArrayHasKey('addresses', $response['customer']);
        $this->assertIsArray(
            [$response['customer']['addresses']],
            "Addresses field must be of an array type."
        );

        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
        $customer = $customerRepository->get($customerFixture->getEmail());
        $this->assertCustomerAddressesFields($customer, $response);
    }

    #[
        DataFixture(CustomerFixture::class, as: 'customer')
    ]
    public function testGetCustomerAddressIfAccountIsLocked(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: The account is locked.');
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        $this->lockCustomer->execute((int)$customer->getId());

        $this->graphQlQuery(
            $this->getCustomerQuery(),
            [],
            '',
            $this->getCustomerAuthHeaders($customer->getEmail())
        );
    }

    #[
        DataFixture(CustomerFixture::class, as: 'customer')
    ]
    public function testGetCustomerAddressIfUserIsNotAuthorized(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: The current customer isn\'t authorized.');

        $this->graphQlQuery($this->getCustomerQuery());
    }

    /**
     * Verify the fields for CustomerAddress object
     *
     * @param CustomerInterface $customer
     * @param array $actualResponse
     * @return void
     * @throws Exception
     */
    private function assertCustomerAddressesFields(CustomerInterface $customer, array $actualResponse): void
    {
        /** @var AddressInterface $addresses */
        $addresses = $customer->getAddresses();
        foreach ($addresses as $addressKey => $addressValue) {
            $this->assertNotEmpty($addressValue);
            $assertionMap = [
                ['response_field' => 'id', 'expected_value' => $addresses[$addressKey]->getId()],
                ['response_field' => 'customer_id', 'expected_value' => 0],
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
     * Get headers with customer authorization token
     *
     * @param string $email
     * @return array
     * @throws Exception
     */
    private function getCustomerAuthHeaders(string $email): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, 'password');

        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    /**
     * Returns the query for customer with addresses
     *
     * @return string
     */
    private function getCustomerQuery(): string
    {
        return <<<QUERY
            {
              customer {
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
    }
}
