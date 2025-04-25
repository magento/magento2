<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GetAllCustomerGroupsTest extends GraphQlAbstract
{
    /**
     * Test to retrieve all customer groups when graphql_share_all_customer_groups is enabled.
     *
     * @throws LocalizedException
     * @throws Exception
     */
    #[
        Config('customer/account_information/graphql_share_all_customer_groups', 1)
    ]
    public function testGetAllCustomerGroups(): void
    {
        self::assertEquals(
            [
                'allCustomerGroups' => $this->fetchAllCustomerGroups()
            ],
            $this->graphQlQuery($this->getAllCustomerGroupsQuery())
        );
    }

    /**
     *  Test to retrieve all customer groups when graphql_share_all_customer_groups is disabled.
     *
     * @throws Exception
     */
    #[
        Config('customer/account_information/graphql_share_all_customer_groups', 0)
    ]
    public function testGetAllCustomerGroupsWhenConfigDisabled(): void
    {
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(
            "Sharing customer group information is disabled or not configured."
        );
        $this->graphQlQuery($this->getAllCustomerGroupsQuery());
    }

    /**
     * Fetch all customer groups
     *
     * @return array|array[]
     * @throws LocalizedException
     */
    public function fetchAllCustomerGroups(): array
    {
        $groupRepository = Bootstrap::getObjectManager()->get(GroupRepositoryInterface::class);
        $searchCriteria = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class)->create();

        $customerGroups = $groupRepository->getList($searchCriteria)->getItems();

        return array_map(
            static fn ($group) => ['name' => $group->getCode()],
            $customerGroups
        );
    }

    /**
     * Get all customer groups query
     *
     * @return string
     */
    private function getAllCustomerGroupsQuery(): string
    {
        return <<<QUERY
            {
              allCustomerGroups {
                name
              }
            }
        QUERY;
    }
}
