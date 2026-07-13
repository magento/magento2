<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for Customer Groups Config Data
 */
class CustomerStoreConfigTest extends GraphQlAbstract
{
    #[
        Config('customer/account_information/graphql_share_customer_group', true)
    ]
    public function testCustomerGroupsGraphQlStoreConfig(): void
    {
        $this->assertEquals(
            [
                'storeConfig' => [
                    'graphql_share_customer_group' => true
                ]
            ],
            $this->graphQlQuery($this->getStoreConfigQuery())
        );
    }

    #[
        Config('customer/account_information/graphql_share_customer_group', false)
    ]
    public function testCustomerGroupsGraphQlStoreConfigDisabled(): void
    {
        $this->assertEquals(
            [
                'storeConfig' => [
                    'graphql_share_customer_group' => false
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
                graphql_share_customer_group
            }
        }
        QUERY;
    }
}
