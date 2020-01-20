<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch6\ConfigurableProduct\Model;

use Magento\ConfigurableProduct\Model\QuickSearchTest as ConfigurableProductQuickSearchTest;

/**
 * Test cases related to find configurable product via quick search using Elasticsearch 6.0+ search engine.
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
     * Assert that configurable child products has not found by query using Elasticsearch 6.0+ search engine.
     *
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     *
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
     *
     * @return void
     */
    public function testChildProductsHasNotFoundedByQuery(): void
    {
        parent::testChildProductsHasNotFoundedByQuery();
    }

    /**
     * Assert that child product of configurable will be available by search after
     * set to product visibility by catalog and search using Elasticsearch 6.0+ search engine.
     *
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     *
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
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
     * Assert that configurable product was found by option value using Elasticsearch 6.0+ search engine.
     *
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     *
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
     *
     * @return void
     */
    public function testSearchByOptionValue(): void
    {
        parent::testSearchByOptionValue();
    }
}
