<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\SalesRule;

use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class CartRulesStoreConfigTest extends GraphQlAbstract
{
    #[
        Config('promo/graphql/share_applied_cart_rule', true)
    ]
    public function testCartRulesGraphQlStoreConfig(): void
    {
        $this->assertEquals(
            [
                'storeConfig' => [
                    'share_applied_cart_rule' => true
                ]
            ],
            $this->graphQlQuery($this->getStoreConfigQuery())
        );
    }

    #[
        Config('promo/graphql/share_applied_cart_rule', false)
    ]
    public function testCartRulesGraphQlStoreConfigDisabled(): void
    {
        $this->assertEquals(
            [
                'storeConfig' => [
                    'share_applied_cart_rule' => false
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
                share_applied_cart_rule
            }
        }
        QUERY;
    }
}
