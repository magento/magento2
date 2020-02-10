<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch7\ConfigurableProduct\Model;

use Magento\ConfigurableProduct\Model\QuickSearchTest as ConfigurableProductQuickSearchTest;
use Magento\TestModuleCatalogSearch\Model\ElasticsearchVersionChecker;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test cases related to find configurable product via quick search using Elasticsearch 7.0+ search engine.
 *
 * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_child_products.php
 * @magentoDataFixture Magento/Elasticsearch7/_files/full_reindex.php
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class QuickSearchTest extends ConfigurableProductQuickSearchTest
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $checker = Bootstrap::getObjectManager()->get(ElasticsearchVersionChecker::class);

        if ($checker->execute() !== 7) {
            $this->markTestSkipped('The installed elasticsearch version isn\'t supported by test');
        }
        parent::setUp();

    }

    /**
     * Assert that configurable child products has not found by query using Elasticsearch 7.0+ search engine.
     *
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     *
     * @magentoConfigFixture default/catalog/search/engine elasticsearch7
     *
     * @return void
     */
    public function testChildProductsHasNotFoundedByQuery(): void
    {
        parent::testChildProductsHasNotFoundedByQuery();
    }

    /**
     * Assert that child product of configurable will be available by search after
     * set to product visibility by catalog and search using Elasticsearch 7.0+ search engine.
     *
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     *
     * @magentoConfigFixture default/catalog/search/engine elasticsearch7
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
     * Assert that configurable product was found by option value using Elasticsearch 7.0+ search engine.
     *
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     *
     * @magentoConfigFixture default/catalog/search/engine elasticsearch7
     *
     * @return void
     */
    public function testSearchByOptionValue(): void
    {
        parent::testSearchByOptionValue();
    }
}
