<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\SalesRule;

use Exception;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for Config Data from Customer->Promotion->GraphQl
 */
class CartRulesStoreConfigTest extends GraphQlAbstract
{

    /**
     * @throws Exception
     */
    #[
        Config('promo/graphql/share_all_sales_rule', 1),
        Config('promo/graphql/share_applied_sales_rule', 1)
    ]
    public function testCartRulesGraphQlStoreConfig(): void
    {
        $this->assertEquals(
            [
                'storeConfig' => [
                    'share_all_sales_rule' => 1,
                    'share_applied_sales_rule' => 1,
                ],
            ],
            $this->graphQlQuery($this->getQuery())
        );
    }

    /**
     * Generates storeConfig query with configurations from promo->graphql
     *
     * @return string
     */
    private function getQuery(): string
    {
        return <<<QUERY
        {
            storeConfig {
                share_all_sales_rule
                share_applied_sales_rule
            }
        }
        QUERY;
    }
}
