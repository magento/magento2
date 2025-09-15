<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GetCustomerGroupTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var Uid
     */
    private $idEncoder;

    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $this->idEncoder = Bootstrap::getObjectManager()->get(Uid::class);
    }
    /**
     * Test to retrieve customer group when graphql_share_customer_group is enabled.
     *
     * @throws LocalizedException
     * @throws Exception
     */
    #[
        Config('customer/account_information/graphql_share_customer_group', true),
        DataFixture(CustomerFixture::class, as: 'customer')
    ]
    public function testGetCustomerGroupForLoggedInCustomer(): void
    {
        $customer = $this->fixtures->get('customer');

        self::assertEquals(
            [
                'customer' => [
                    'group' => [
                        'uid' => $this->idEncoder->encode($customer->getGroupId())
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCustomerGroupQueryForLoggedInCustomer(),
                [],
                '',
                $this->getCustomerAuthHeaders($customer->getEmail())
            )
        );
    }

    /**
     *  Test to retrieve customer group when graphql_share_customer_group is disabled.
     *
     * @throws Exception
     */
    #[
        Config('customer/account_information/graphql_share_customer_group', false),
        DataFixture(CustomerFixture::class, as: 'customer')
    ]
    public function testGetCustomerGroupForLoggedInCustomerWhenConfigDisabled(): void
    {
        self::assertEquals(
            [
                'customer' => [
                    'group' => null
                ]
            ],
            $this->graphQlQuery(
                $this->getCustomerGroupQueryForLoggedInCustomer(),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    #[
        Config('customer/account_information/graphql_share_customer_group', true),
        DataFixture(CustomerFixture::class, as: 'customer')
    ]
    public function testGetCustomerGroup(): void
    {
        $customer = $this->fixtures->get('customer');

        self::assertEquals(
            [
                'customerGroup' => [
                    'uid' => $this->idEncoder->encode($customer->getGroupId())
                ]
            ],
            $this->graphQlQuery(
                $this->getCustomerGroupQuery(),
                [],
                '',
                $this->getCustomerAuthHeaders($customer->getEmail())
            )
        );
    }

    /**
     * @throws Exception
     */
    #[
        Config('customer/account_information/graphql_share_customer_group', false),
        DataFixture(CustomerFixture::class, as: 'customer')
    ]
    public function testGetCustomerGroupWhenConfigDisabled(): void
    {
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(
            "Sharing customer group information is disabled or not configured."
        );
        $this->graphQlQuery(
            $this->getCustomerGroupQuery(),
            [],
            '',
            $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
        );
    }

    /**
     * @throws Exception
     */
    #[
        Config('customer/account_information/graphql_share_customer_group', true)
    ]
    public function testGetCustomerGroupForGuest(): void
    {
        self::assertEquals(
            [
                'customerGroup' => [
                    'uid' => $this->idEncoder->encode('0')
                ]
            ],
            $this->graphQlQuery($this->getCustomerGroupQuery())
        );
    }

    /**
     * @throws Exception
     */
    #[
        Config('customer/account_information/graphql_share_customer_group', false)
    ]
    public function testGetCustomerGroupForGuestWhenConfigDisabled(): void
    {
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(
            "Sharing customer group information is disabled or not configured."
        );
        $this->graphQlQuery($this->getCustomerGroupQuery());
    }

    /**
     * Get customer groups query
     *
     * @return string
     */
    private function getCustomerGroupQuery(): string
    {
        return <<<QUERY
            query CustomerGroup {
                customerGroup {
                    uid
                }
            }
        QUERY;
    }

    /**
     * Get customer groups query for the logged in customer
     *
     * @return string
     */
    private function getCustomerGroupQueryForLoggedInCustomer(): string
    {
        return <<<QUERY
            {
              customer {
                group {
                  uid
                }
              }
            }
        QUERY;
    }

    /**
     * Retrieve Auth header for customer with email
     *
     * @param string $email
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, 'password');
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
