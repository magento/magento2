<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Config;

use Magento\CatalogGraphQl\Model\Resolver\Products\Attributes\Collection as ProductsAttributesCollection;
use Magento\Framework\App\Area;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Exception\LocalizedException;
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
     * GraphQL configuration must not be loaded with dependencies from another area.
     *
     * @return void
     * @magentoAppArea global
     */
    public function testGraphQlConfigCannotBeLoadedOutsideGraphQlArea(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Configuration can only be loaded in the "graphql" area.');

        $objectManager = Bootstrap::getObjectManager();
        $this->assertSame(
            Area::AREA_GLOBAL,
            $objectManager->get(ScopeInterface::class)->getCurrentScope()
        );
        $objectManager->create(\Magento\Framework\GraphQl\Config\Data::class);
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
