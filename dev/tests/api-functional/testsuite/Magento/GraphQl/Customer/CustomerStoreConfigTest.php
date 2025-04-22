<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for Customer Groups Config Data
 */
class CustomerStoreConfigTest extends GraphQlAbstract
{
    /**
     * @throws Exception
     */
    #[
        Config('customer/account_information/graphql_share_all_customer_groups', 1),
        Config('customer/account_information/graphql_share_customer_group', 1)
    ]
    public function testCustomerGroupsGraphQlStoreConfig(): void
    {
        $this->assertEquals(
            [
                'storeConfig' => [
                    'graphql_share_all_customer_groups' => 1,
                    'graphql_share_customer_group' => 1
                ]
            ],
            $this->graphQlQuery($this->getStoreConfigQuery())
        );
    }

    /**
     * Generates storeConfig query with newly added configurations
     *
     * @return string
     */
    private function getStoreConfigQuery(): string
    {
        return <<<QUERY
        {
            storeConfig {
                graphql_share_all_customer_groups
                graphql_share_customer_group
            }
        }
        QUERY;
    }
}
