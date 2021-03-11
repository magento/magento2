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
 * Test cases related to find configurable product via quick search using mysql search engine.
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
    protected function setUp(): void
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
    public function testChildProductsHasNotFoundedByQuery(): void
    {
        $this->checkThatOnlyConfigurableProductIsAvailableBySearch('Configurable Option');
    }

    /**
     * Assert that child product of configurable will be available by search after
     * set to product visibility by catalog and search using mysql search engine.
     *
     * @magentoConfigFixture default/catalog/search/engine mysql
     * @dataProvider productAvailabilityInSearchByVisibilityDataProvider
     *
     * @param int $visibility
     * @param bool $expectedResult
     * @return void
     */
    public function testOneOfChildIsAvailableBySearch(int $visibility, bool $expectedResult): void
    {
        $this->checkThatOnlyConfigurableProductIsAvailableBySearch('Configurable Option');
        $this->updateProductVisibility($visibility);
        $this->checkProductAvailabilityInSearch($expectedResult);
        $this->checkThatOnlyConfigurableProductIsAvailableBySearch('White');
    }

    /**
     * Return data with product visibility and expected result.
     *
     * @return array
     */
    public function productAvailabilityInSearchByVisibilityDataProvider(): array
    {
        return [
            'visible_catalog_only' => [
                Visibility::VISIBILITY_IN_CATALOG,
                false,
            ],
            'visible_catalog_and_search' => [
                Visibility::VISIBILITY_BOTH,
                true,
            ],
            'visible_search_only' => [
                Visibility::VISIBILITY_IN_SEARCH,
                true,
            ],
            'visible_search_not_visible_individuality' => [
                Visibility::VISIBILITY_NOT_VISIBLE,
                false,
            ],
        ];
    }

    /**
     * Assert that configurable product was found by option value using mysql search engine.
     *
     * @magentoConfigFixture default/catalog/search/engine mysql
     *
     * @return void
     */
    public function testSearchByOptionValue(): void
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

    /**
     * Update product visibility.
     *
     * @param int $visibility
     * @return void
     */
    private function updateProductVisibility(int $visibility): void
    {
        $childProduct = $this->productRepository->get('Simple option 1');
        $childProduct->setVisibility($visibility);
        $this->productRepository->save($childProduct);
    }

    /**
     * Assert that configurable and one of child product is available by search.
     *
     * @param bool $firstChildIsVisible
     * @return void
     */
    private function checkProductAvailabilityInSearch(bool $firstChildIsVisible): void
    {
        $searchResult = $this->quickSearchByQuery->execute('Black');
        $this->assertNotNull($searchResult->getItemByColumnValue(Product::SKU, 'Configurable product'));
        $this->assertEquals(
            $firstChildIsVisible,
            (bool)$searchResult->getItemByColumnValue(Product::SKU, 'Simple option 1')
        );
        $this->assertNull($searchResult->getItemByColumnValue(Product::SKU, 'Simple option 2'));
    }
}
