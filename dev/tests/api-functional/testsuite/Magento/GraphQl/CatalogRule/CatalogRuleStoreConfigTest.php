<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogRule;

use Exception;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test coverage for Catalog Rule Config Data
 */
class CatalogRuleStoreConfigTest extends GraphQlAbstract
{
    /**
     * @throws Exception
     */
    #[
        Config('catalog/rule/share_all_catalog_rules', 1),
        Config('catalog/rule/share_applied_catalog_rules', 1)
    ]
    public function testCatalogRuleGraphQlStoreConfig(): void
    {
        $this->assertEquals(
            [
                'storeConfig' => [
                    'share_all_catalog_rules' => 1,
                    'share_applied_catalog_rules' => 1
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
                share_all_catalog_rules
                share_applied_catalog_rules
            }
        }
        QUERY;
    }
}
