<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation\Category;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

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
     * @param int $filterable
     * @param array $expectation
     * @return void
     */
    public function testGetFiltersWithCustomAttribute(array $products, int $filterable, array $expectation): void
    {
        $this->updateAttributeAndProducts('boolean_attribute', $filterable, $products);
        $this->prepareNavigationBlock('Category 999');
        $filter = $this->getFilterByCode($this->navigationBlock->getFilters(), 'boolean_attribute');

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
                'expectation' => [],
            ],
            'used_in_navigation_with_results' => [
                'products_data' => [
                    'simple1000' => 'Yes',
                    'simple1001' => 'Yes',
                ],
                'filterable' => AbstractFilter::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS,
                'expectation' => [
                    ['label' => 'Yes', 'count' => 2],
                ],
            ],
            'used_in_navigation_without_results' => [
                'products_data' => [
                    'simple1000' => 'Yes',
                    'simple1001' => 'Yes',
                ],
                'filterable' => 2,
                'expectation' => [
                    ['label' => 'Yes', 'count' => 2],
                    ['label' => 'No', 'count' => 0],
                ],
            ],
        ];
    }
}
