<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Config;

use Magento\CatalogGraphQl\Model\Resolver\Products\Attributes\Collection as ProductsAttributesCollection;
use Magento\Framework\Search\Request\Config as SearchRequestConfig;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;

/**
 * @magentoDbIsolation disabled
 * @magentoAppArea graphql
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var bool|null $isFixtureLoaded
     */
    private ?bool $isFixtureLoaded = null;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->isFixtureLoaded = false;
        CacheCleaner::cleanAll();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        if ($this->isFixtureLoaded) {
            $rollBackPath = __DIR__
                . '/../../../Catalog/_files/products_with_layered_navigation_attribute_store_options_rollback.php';
            require $rollBackPath;
        }
    }

    /**
     * Test to confirm that we don't load cached configuration before attribute existed
     *
     * @covers \Magento\Framework\Search\Request\Config
     * @return void
     * @magentoApiDataFixture Magento/Store/_files/store.php
     */
    public function testAttributesChangeCleansSearchRequestConfigCache(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var SearchRequestConfig $configInstance1 */
        $configInstance1 = $objectManager->create(SearchRequestConfig::class);
        $aggregations1 = $configInstance1->get('graphql_product_search_with_aggregation/aggregations');
        $this->assertArrayNotHasKey('test_configurable_bucket', $aggregations1);
        require __DIR__ . '/../../../Catalog/_files/products_with_layered_navigation_attribute_store_options.php';
        $this->isFixtureLoaded = true;
        /** @var SearchRequestConfig $configInstance2 */
        $configInstance2 = $objectManager->create(SearchRequestConfig::class);
        $aggregations2 = $configInstance2->get('graphql_product_search_with_aggregation/aggregations');
        $this->assertArrayHasKey('test_configurable_bucket', $aggregations2);
    }

    /**
     * Test to confirm that we don't load cached configuration before attribute existed
     *
     * @return void
     * @magentoApiDataFixture Magento/Store/_files/store.php
     */
    public function testAttributesChangeCleansGraphQlConfigCache(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->resetStateProductsAttributesCollection($objectManager);
        $configInstance1 = $objectManager->create('Magento\Framework\GraphQl\Config\Data');
        $aggregations1 = $configInstance1->get('SimpleProduct/fields');
        $this->assertArrayNotHasKey('test_configurable', $aggregations1);
        require __DIR__ . '/../../../Catalog/_files/products_with_layered_navigation_attribute_store_options.php';
        $this->isFixtureLoaded = true;
        $this->resetStateProductsAttributesCollection($objectManager);
        $configInstance2 = $objectManager->create('Magento\Framework\GraphQl\Config\Data');
        $aggregations2 = $configInstance2->get('SimpleProduct/fields');
        $this->assertArrayHasKey('test_configurable', $aggregations2);
    }

    /**
     * Test to confirm that we don't load cached configuration before attribute existed
     *
     * @return void
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @magentoAppArea global
     */
    public function testGraphQlConfigCacheShouldntBreakWhenLoadedByGlobalArea(): void
    {
        /*
         * When Magento\Framework\GraphQl\Config\Data is loaded from area outside of graphql, and its cache doesn't
         * currently exist, then it will load incorrect data and save it in its cache, which will then be used by
         * Magento\Framework\GraphQl\Config\Data when it actually is in the graphql area.
         * See AC-10465 for more details on this bug.
         */
        $this->markTestSkipped('Fix this in AC-10465');
        $this->testAttributesChangeCleansGraphQlConfigCache();
    }

    /**
     * Magento\CatalogGraphQl\Model\Config\AttributeReader has mutable state in
     * Magento\CatalogGraphQl\Model\Resolver\Products\Attributes\Collection $collection, so we must reset it.
     *
     * @param $objectManager
     * @return void
     */
    private function resetStateProductsAttributesCollection($objectManager) : void
    {
        /** @var ProductsAttributesCollection $productsAttributesCollection */
        $productsAttributesCollection = $objectManager->get(ProductsAttributesCollection::class);
        $productsAttributesCollection->_resetState();
    }
}
