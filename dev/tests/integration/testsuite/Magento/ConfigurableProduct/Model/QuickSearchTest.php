<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\TestFramework\Catalog\Model\Layer\QuickSearchByQuery;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test cases related to find configurable product via quick search.
 *
 * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_child_products.php
 * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class QuickSearchTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var QuickSearchByQuery
     */
    private $quickSearchByQuery;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->quickSearchByQuery = $this->objectManager->get(QuickSearchByQuery::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        parent::setUp();
    }

    /**
     * Assert that configurable child products has not found by query using mysql search engine.
     *
     * @magentoConfigFixture default/catalog/search/engine mysql
     *
     * @return void
     */
    public function testChildProductsHasNotFoundedByQueryUsingMysql(): void
    {
        $this->checkThatOnlyConfigurableProductIsAvailableBySearch('Configurable Option');
    }

    /**
     * Assert that configurable child products has not found by query using Elasticsearch 6.0+ search engine.
     *
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
     *
     * @return void
     */
    public function testChildProductsHasNotFoundedByQueryUsingElasticsearch(): void
    {
        $this->checkThatOnlyConfigurableProductIsAvailableBySearch('Configurable Option');
    }

    /**
     * Assert that child product of configurable will be available by search after
     * set to product visibility by catalog and search using mysql search engine.
     *
     * @magentoConfigFixture default/catalog/search/engine mysql
     *
     * @return void
     */
    public function testOneOfChildIsAvailableBySearchUsingMysql(): void
    {
        $this->checkThatOnlyConfigurableProductIsAvailableBySearch('Configurable Option');
        $childProduct = $this->productRepository->get('Simple option 1');
        $childProduct->setVisibility(Visibility::VISIBILITY_BOTH);
        $this->productRepository->save($childProduct);
        $searchResult = $this->quickSearchByQuery->execute('Black');
        $this->assertNotNull($searchResult->getItemByColumnValue(Product::SKU, 'Configurable product'));
        $this->assertNotNull($searchResult->getItemByColumnValue(Product::SKU, 'Simple option 1'));
        $this->assertNull($searchResult->getItemByColumnValue(Product::SKU, 'Simple option 2'));
        $this->checkThatOnlyConfigurableProductIsAvailableBySearch('White');
    }

    /**
     * Assert that child product of configurable will be available by search after
     * set to product visibility by catalog and search using Elasticsearch 6.0+ search engine.
     *
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
     *
     * @return void
     */
    public function testOneOfChildIsAvailableBySearchUsingElasticsearch(): void
    {
        $this->testOneOfChildIsAvailableBySearchUsingMysql();
    }

    /**
     * Assert that configurable product was found by option value using mysql search engine.
     *
     * @magentoConfigFixture default/catalog/search/engine mysql
     *
     * @return void
     */
    public function testSearchByOptionValueUsingMysql(): void
    {
        $this->checkThatOnlyConfigurableProductIsAvailableBySearch('Option 1');
    }

    /**
     * Assert that configurable product was found by option value using Elasticsearch 6.0+ search engine.
     *
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
     *
     * @return void
     */
    public function testSearchByOptionValueUsingElasticsearch(): void
    {
        $this->checkThatOnlyConfigurableProductIsAvailableBySearch('Option 1');
    }

    /**
     * Assert that anyone child product is not available by quick search.
     *
     * @param string $searchQuery
     *
     * @return void
     */
    private function checkThatOnlyConfigurableProductIsAvailableBySearch(string $searchQuery): void
    {
        $searchResult = $this->quickSearchByQuery->execute($searchQuery);
        $this->assertCount(1, $searchResult->getItems());
        /** @var Product $configurableProduct */
        $configurableProduct = $searchResult->getFirstItem();
        $this->assertEquals('Configurable product', $configurableProduct->getSku());
    }
}
