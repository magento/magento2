<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation\Category;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\LayeredNavigation\Block\Navigation\AbstractFiltersTest;

/**
 * Provides tests for custom boolean filter in navigation block on category page.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class BooleanFilterTest extends AbstractFiltersTest
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_boolean_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/category_with_different_price_products.php
     * @dataProvider getFiltersWithCustomAttributeDataProvider
     * @param array $products
     * @param array $attributeData
     * @param array $expectation
     * @return void
     */
    public function testGetFiltersWithCustomAttribute(array $products, array $attributeData, array $expectation): void
    {
        $this->getCategoryFiltersAndAssert($products, $attributeData, $expectation, 'Category 999');
    }

    /**
     * @return array
     */
    public function getFiltersWithCustomAttributeDataProvider(): array
    {
        return [
            'not_used_in_navigation' => [
                'products_data' => [],
                'attribute_data' => ['is_filterable' => 0],
                'expectation' => [],
            ],
            'used_in_navigation_with_results' => [
                'products_data' => [
                    'simple1000' => 'Yes',
                    'simple1001' => 'Yes',
                ],
                'attribute_data' => ['is_filterable' => AbstractFilter::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS],
                'expectation' => [
                    ['label' => 'Yes', 'count' => 2],
                ],
            ],
            'used_in_navigation_without_results' => [
                'products_data' => [
                    'simple1000' => 'Yes',
                    'simple1001' => 'Yes',
                ],
                'attribute_data' => ['is_filterable' => 2],
                'expectation' => [
                    ['label' => 'Yes', 'count' => 2],
                    ['label' => 'No', 'count' => 0],
                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_boolean_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/category_with_different_price_products.php
     * @dataProvider getActiveFiltersWithCustomAttributeDataProvider
     * @param array $products
     * @param array $expectation
     * @param string $filterValue
     * @param int $productsCount
     * @return void
     */
    public function testGetActiveFiltersWithCustomAttribute(
        array $products,
        array $expectation,
        string $filterValue,
        int $productsCount
    ): void {
        $this->getCategoryActiveFiltersAndAssert($products, $expectation, 'Category 999', $filterValue, $productsCount);
    }

    /**
     * @return array
     */
    public function getActiveFiltersWithCustomAttributeDataProvider(): array
    {
        return [
            'selected_yes_option_in_all_products' => [
                'products_data' => ['simple1000' => 'Yes', 'simple1001' => 'Yes'],
                'expectation' => ['label' => 'Yes', 'count' => 0],
                'filter_value' => 'Yes',
                'products_count' => 2,
            ],
            'selected_yes_option_in_one_product' => [
                'products_data' => ['simple1000' => 'Yes', 'simple1001' => 'No'],
                'expectation' => ['label' => 'Yes', 'count' => 0],
                'filter_value' => 'Yes',
                'products_count' => 1,
            ],
            'selected_no_option_in_all_products' => [
                'products_data' => ['simple1000' => 'No', 'simple1001' => 'No'],
                'expectation' => ['label' => 'No', 'count' => 0],
                'filter_value' => 'No',
                'products_count' => 2,
            ],
            'selected_no_option_in_one_product' => [
                'products_data' => ['simple1000' => 'Yes', 'simple1001' => 'No'],
                'expectation' => ['label' => 'No', 'count' => 0],
                'filter_value' => 'No',
                'products_count' => 1,
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
        return 'boolean_attribute';
    }
}
