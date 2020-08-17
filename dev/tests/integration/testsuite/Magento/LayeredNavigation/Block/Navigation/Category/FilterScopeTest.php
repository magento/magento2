<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation\Category;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\LayeredNavigation\Block\Navigation\AbstractFiltersTest;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provides tests for custom filter with different scopes in navigation block on category page.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class FilterScopeTest extends AbstractFiltersTest
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int
     */
    private $oldStoreId;

    /**
     * @var int
     */
    private $currentStoreId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->oldStoreId = (int)$this->storeManager->getStore()->getId();
        $this->currentStoreId = (int)$this->storeManager->getStore('fixture_second_store')->getId();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_dropdown_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/category_with_different_price_products_on_two_websites.php
     * @dataProvider filtersWithScopeDataProvider
     * @param int $scope
     * @param array $products
     * @param array $expectation
     * @return void
     */
    public function testGetFilters(int $scope, array $products, array $expectation): void
    {
        $this->updateAttribute(
            [
                'is_filterable' => AbstractFilter::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS,
                'is_global' => $scope,
            ]
        );
        $this->updateProductsOnStore($products);
        $this->clearInstanceAndReindexSearch();
        try {
            $this->storeManager->setCurrentStore($this->currentStoreId);
            $this->navigationBlock->getLayer()->setCurrentCategory(
                $this->loadCategory('Category 999', $this->currentStoreId)
            );
            $this->navigationBlock->setLayout($this->layout);
            $filter = $this->getFilterByCode($this->navigationBlock->getFilters(), $this->getAttributeCode());
            $this->assertNotNull($filter);
            $this->assertEquals($expectation, $this->prepareFilterItems($filter));
        } finally {
            $this->storeManager->setCurrentStore($this->oldStoreId);
        }
    }

    /**
     * @return array
     */
    public function filtersWithScopeDataProvider(): array
    {
        return [
            'with_scope_store' => [
                'scope' => ScopedAttributeInterface::SCOPE_STORE,
                'products' => [
                    'default' => ['simple1000' => 'Option 1', 'simple1001' => 'Option 2'],
                    'fixture_second_store' => ['simple1000' => 'Option 2', 'simple1001' => 'Option 3'],
                ],
                'expectation' => [
                    ['label' => 'Option 2', 'count' => 1],
                    ['label' => 'Option 3', 'count' => 1],
                ],
            ],
            'with_scope_website' => [
                'scope' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'products' => [
                    'default' => ['simple1000' => 'Option 3', 'simple1001' => 'Option 2'],
                    'fixture_second_store' => ['simple1000' => 'Option 1', 'simple1001' => 'Option 2'],
                ],
                'expectation' => [
                    ['label' => 'Option 1', 'count' => 1],
                    ['label' => 'Option 2', 'count' => 1],
                ],
            ],
            'with_scope_global' => [
                'scope' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'products' => [
                    'default' => ['simple1000' => 'Option 1'],
                    'fixture_second_store' => ['simple1001' => 'Option 2'],
                ],
                'expectation' => [
                    ['label' => 'Option 1', 'count' => 1],
                    ['label' => 'Option 2', 'count' => 1],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getLayerType(): string
    {
        return Resolver::CATALOG_LAYER_CATEGORY;
    }

    /**
     * @inheritdoc
     */
    protected function getAttributeCode(): string
    {
        return 'dropdown_attribute';
    }

    /**
     * Updates products data for store.
     *
     * @param array $productsData
     * @return void
     */
    private function updateProductsOnStore(array $productsData): void
    {
        try {
            foreach ($productsData as $storeCode => $products) {
                $storeId = (int)$this->storeManager->getStore($storeCode)->getId();
                $this->storeManager->setCurrentStore($storeId);
                $this->updateProducts($products, $this->getAttributeCode(), $storeId);
            }
        } finally {
            $this->storeManager->setCurrentStore($this->oldStoreId);
        }
    }
}
