<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogRule;

use Exception;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogRule\Test\Fixture\Rule as CatalogRuleFixture;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GetAppliedCatalogRulesTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test to retrieve applied catalog rules when catalog/rule/share_applied_catalog_rules is enabled.
     *
     * @throws Exception
     */
    #[
        Config('catalog/rule/share_applied_catalog_rules', 1),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CatalogRuleFixture::class, as: 'catalogrule1')
    ]
    public function testGetAppliedCatalogRules(): void
    {
        $response = $this->graphQlQuery($this->getAppliedCatalogRulesQuery());
        $this->assertContains(
            $this->fixtures->get('catalogrule1')->getName(),
            array_column($response['products']['items'][0]['rules'], 'name')
        );
    }

    /**
     * Test to retrieve applied catalog rules when catalog/rule/share_applied_catalog_rules is enabled.
     *
     * @throws Exception
     */
    #[
        Config('catalog/rule/share_applied_catalog_rules', 1),
        DataFixture(ProductFixture::class, as: 'product')
    ]
    public function testGetAppliedCatalogRulesWithZeroResult(): void
    {
        $response = $this->graphQlQuery($this->getAppliedCatalogRulesQuery());
        $this->assertEmpty($response['products']['items'][0]['rules']);
    }

    /**
     *  Test to retrieve applied catalog rules when catalog/rule/share_applied_catalog_rules is disabled.
     *
     * @throws Exception
     */
    #[
        Config('catalog/rule/share_applied_catalog_rules', 0),
        DataFixture(ProductFixture::class, as: 'product')
    ]
    public function testGetAppliedCatalogRulesWhenConfigDisabled(): void
    {
        self::assertEquals(
            [
                'products' => [
                    'items' => [
                        '0' => [
                            'rules' => null
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery($this->getAppliedCatalogRulesQuery())
        );
    }

    /**
     * Get applied catalog rules query
     *
     * @return string
     */
    private function getAppliedCatalogRulesQuery(): string
    {
        return <<<QUERY
            {
              products (filter: { sku: { eq: "{$this->fixtures->get('product')->getSku()}" } }){
                items {
                  rules {
                    name
                  }
                }
              }
            }
        QUERY;
    }
}
