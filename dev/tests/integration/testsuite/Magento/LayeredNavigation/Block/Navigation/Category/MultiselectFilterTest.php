<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation\Category;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

/**
 * Provides tests for custom multiselect filter in navigation block on category page.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class MultiselectFilterTest extends AbstractFiltersTest
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/multiselect_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/category_with_different_price_products.php
     * @dataProvider getFiltersWithCustomAttributeDataProvider
     * @param array $products
     * @param int $filterable
     * @param array $expectation
     * @return void
     */
    public function testGetFiltersWithCustomAttribute(array $products, int $filterable, array $expectation): void
    {
        $this->updateAttributeAndProducts('multiselect_attribute', $filterable, $products);
        $this->prepareNavigationBlock('Category 999');
        $filter = $this->getFilterByCode($this->navigationBlock->getFilters(), 'multiselect_attribute');

        if ($filterable) {
            $this->assertNotNull($filter);
            $this->assertEquals($expectation, $this->prepareFilterItems($filter));
        } else {
            $this->assertNull($filter);
        }
    }

    /**
     * @return array
     */
    public function getFiltersWithCustomAttributeDataProvider(): array
    {
        return [
            'not_used_in_navigation' => [
                'products_data' => [],
                'filterable' => 0,
                'expectation' => []
            ],
            'used_in_navigation_with_results' => [
                'products_data' => [
                    'simple1000' => 'Option 1',
                    'simple1001' => 'Option 2',
                ],
                'filterable' => AbstractFilter::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS,
                'expectation' => [
                    ['label' => 'Option 1', 'count' => 1],
                    ['label' => 'Option 2', 'count' => 1],
                ]
            ],
            'used_in_navigation_without_results' => [
                'products_data' => [
                    'simple1000' => 'Option 1',
                    'simple1001' => 'Option 2',
                ],
                'filterable' => 2,
                'expectation' => [
                    ['label' => 'Option 1', 'count' => 1],
                    ['label' => 'Option 2', 'count' => 1],
                    ['label' => 'Option 3', 'count' => 0],
                    ['label' => 'Option 4 "!@#$%^&*', 'count' => 0],
                ]
            ],
        ];
    }
}
