<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\ConfigurableProduct\Model;

use Magento\ConfigurableProduct\Model\QuickSearchTest as ConfigurableProductQuickSearchTest;
use Magento\TestModuleCatalogSearch\Model\ElasticsearchVersionChecker;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Search\EngineResolverInterface;

/**
 * Test cases related to find configurable product via quick search using Elasticsearch search engine.
 *
 * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_child_products.php
 * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class QuickSearchTest extends ConfigurableProductQuickSearchTest
{

    /**
     * @inheritdoc
     */
    protected function assertPreConditions(): void
    {
        // phpstan:ignore "Class Magento\TestModuleCatalogSearch\Model\ElasticsearchVersionChecker not found."
        $version = Bootstrap::getObjectManager()->get(ElasticsearchVersionChecker::class)->getVersion();
        $searchEngine = 'elasticsearch' . $version;
        $currentEngine = Bootstrap::getObjectManager()->get(EngineResolverInterface::class)->getCurrentSearchEngine();
        $this->assertEquals($searchEngine, $currentEngine);
    }

    /**
     * Assert that configurable child products has not found by query using Elasticsearch
     * search engine.
     *
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     *
     * @return void
     */
    public function testChildProductsHasNotFoundedByQuery(): void
    {
        parent::testChildProductsHasNotFoundedByQuery();
    }

    /**
     * Assert that child product of configurable will be available by search after
     * set to product visibility by catalog and search using Elasticsearch search engine.
     *
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     *
     * @dataProvider productAvailabilityInSearchByVisibilityDataProvider
     *
     * @param int $visibility
     * @param bool $expectedResult
     * @return void
     */
    public function testOneOfChildIsAvailableBySearch(int $visibility, bool $expectedResult): void
    {
        parent::testOneOfChildIsAvailableBySearch($visibility, $expectedResult);
    }

    /**
     * Assert that configurable product was found by option value using Elasticsearch search engine.
     *
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     *
     * @return void
     */
    public function testSearchByOptionValue(): void
    {
        parent::testSearchByOptionValue();
    }
}
