<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation\Category;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\ScopeInterface;
use Magento\LayeredNavigation\Block\Navigation\AbstractFiltersTest;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Store\Model\ScopeInterface as StoreScope;

/**
 * Provides tests for select filter in navigation block on category page with out of stock products
 * and enabled out of stock products displaying.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class OutOfStockProductsFilterTest extends AbstractFiltersTest
{
    /**
     * @var MutableScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->scopeConfig = $this->objectManager->get(MutableScopeConfigInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_dropdown_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/out_of_stock_product_with_category.php
     * @magentoDataFixture Magento/Catalog/_files/product_with_category.php
     * @dataProvider getFiltersWithOutOfStockProduct
     * @param int $showOutOfStock
     * @param array $expectation
     * @return void
     */
    public function testGetFiltersWithOutOfStockProduct(int $showOutOfStock, array $expectation): void
    {
        $this->updateConfigShowOutOfStockFlag($showOutOfStock);
        $this->getCategoryFiltersAndAssert(
            ['out-of-stock-product' => 'Option 1', 'in-stock-product' => 'Option 2'],
            ['is_filterable' => AbstractFilter::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS],
            $expectation,
            'Category 1'
        );
    }

    /**
     * @return array
     */
    public static function getFiltersWithOutOfStockProduct(): array
    {
        return [
            'show_out_of_stock' => [
                'showOutOfStock' => 1,
                'expectation' => [['label' => 'Option 1', 'count' => 1], ['label' => 'Option 2', 'count' => 1]],
            ],
            'not_show_out_of_stock' => [
                'showOutOfStock' => 0,
                'expectation' => [['label' => 'Option 2', 'count' => 1]],
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
     * Updates store config 'cataloginventory/options/show_out_of_stock' flag.
     *
     * @param int $showOutOfStock
     * @return void
     */
    protected function updateConfigShowOutOfStockFlag(int $showOutOfStock): void
    {
        $this->scopeConfig->setValue(
            Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
            $showOutOfStock,
            StoreScope::SCOPE_STORE,
            ScopeInterface::SCOPE_DEFAULT
        );
    }
}
