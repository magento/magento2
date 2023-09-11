<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation\Search;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\LayeredNavigation\Block\Navigation\Category\BooleanFilterTest as CategoryBooleanFilterTest;

/**
 * Provides tests for custom boolean filter in navigation block on search page.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class BooleanFilterTest extends CategoryBooleanFilterTest
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
    public function testGetFiltersWithCustomAttribute(
        array $products,
        array $attributeData,
        array $expectation
    ): void {
        $this->getSearchFiltersAndAssert($products, $attributeData, $expectation);
    }

    /**
     * @return array
     */
    public function getFiltersWithCustomAttributeDataProvider(): array
    {
        $dataProvider = parent::getFiltersWithCustomAttributeDataProvider();

        $dataProvider = array_replace_recursive(
            $dataProvider,
            [
                'not_used_in_navigation' => [
                    'attribute_data' => [
                        'is_filterable_in_search' => 0,
                    ],
                ],
                'used_in_navigation_with_results' => [
                    'attribute_data' => [
                        'is_filterable_in_search' => 1,
                    ],
                ],
                'used_in_navigation_without_results' => [
                    'attribute_data' => [
                        'is_filterable' => 0,
                        'is_filterable_in_search' => 1,
                    ],
                ],
            ]
        );
        //TODO uncomment after fix MC-29227
        //unset($dataProvider['used_in_navigation_without_results']['expectation'][1]);

        return $dataProvider;
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
        $this->getSearchActiveFiltersAndAssert($products, $expectation, $filterValue, $productsCount);
    }

    /**
     * @inheritdoc
     */
    protected function getLayerType(): string
    {
        return Resolver::CATALOG_LAYER_SEARCH;
    }
}
