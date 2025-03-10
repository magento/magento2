<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\Data\GroupExtension;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Group;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Customer\Test\Fixture\CustomerGroup as CustomerGroupFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
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
    private const GUEST_CUSTOMER_GROUP = 'NOT LOGGED IN';

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $this->groupRepository = Bootstrap::getObjectManager()->get(GroupRepositoryInterface::class);
    }
    /**
     * Test to retrieve customer group when graphql_share_customer_group is enabled.
     *
     * @throws LocalizedException
     * @throws Exception
     */
    #[
        Config('customer/account_information/graphql_share_customer_group', 1),
        DataFixture(CustomerFixture::class, as: 'customer')
    ]
    public function testGetCustomerGroupForLoggedInCustomer(): void
    {
        $customer = $this->fixtures->get('customer');

        self::assertEquals(
            [
                'customer' => [
                    'group' => [
                        'name' => $this->groupRepository->getById($customer->getGroupId())->getCode()
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
     * Test to retrieve customer group when graphql_share_customer_group is disabled.
     *
     * @throws Exception
     */
    #[
        Config('customer/account_information/graphql_share_customer_group', 0),
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

    /**
     * @throws Exception
     */
    #[
        Config('customer/account_information/graphql_share_customer_group', 1),
        DataFixture(CustomerFixture::class, as: 'customer')
    ]
    public function testGetCustomerGroup(): void
    {
        $customer = $this->fixtures->get('customer');

        self::assertEquals(
            [
                'customerGroup' => [
                    'name' => $this->groupRepository->getById($customer->getGroupId())->getCode()
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
        Config('customer/account_information/graphql_share_customer_group', 1),
        DataFixture(CustomerGroupFixture::class, as: 'group')
    ]
    public function testGetCustomerGroupWhenItsExcluded(): void
    {
        $customer = Bootstrap::getObjectManager()->get(Customer::class);
        $customerGroup = Bootstrap::getObjectManager()->get(Group::class);

        // Load Customer Group
        $customerGroup->load($this->fixtures->get('group')->getCode(), 'customer_group_code');

        // Ensure extension attributes exist
        $extensionAttributes = $customerGroup->getExtensionAttributes();
        if (!$extensionAttributes) {
            $extensionAttributes = Bootstrap::getObjectManager()->get(GroupExtension::class);
            $customerGroup->setExtensionAttributes($extensionAttributes);
        }

        // Set excluded website ID
        $extensionAttributes->setExcludeWebsiteIds([1]); // Website ID 1 is excluded
        $customerGroup->setExtensionAttributes($extensionAttributes);
        $customerGroup->save();

        //set customer
        $customer->setWebsiteId(1);
        $customer->setGroupId($customerGroup->getId());
        $customer->setEmail('excluded_customer@example.com');
        $customer->setFirstname('Excluded');
        $customer->setLastname('User');
        $customer->setPassword('password');
        $customer->save();

        $response = $this->graphQlQuery(
            $this->getCustomerGroupQuery(),
            [],
            '',
            $this->getCustomerAuthHeaders('excluded_customer@example.com')
        );

        self::assertNotEquals($customer->getCustomerGroup(), $response['customerGroup']['name']);
    }

    /**
     * @throws Exception
     */
    #[
        Config('customer/account_information/graphql_share_customer_group', 0),
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
        Config('customer/account_information/graphql_share_customer_group', 1)
    ]
    public function testGetCustomerGroupForGuest(): void
    {
        self::assertEquals(
            [
                'customerGroup' => [
                    'name' => self::GUEST_CUSTOMER_GROUP
                ]
            ],
            $this->graphQlQuery($this->getCustomerGroupQuery())
        );
    }

    /**
     * @throws Exception
     */
    #[
        Config('customer/account_information/graphql_share_customer_group', 0)
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
                    name
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
                  name
                }
              }
            }
        QUERY;
    }

    /**
     * Get customer auth headers
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
