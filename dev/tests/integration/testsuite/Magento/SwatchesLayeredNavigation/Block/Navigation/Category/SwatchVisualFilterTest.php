<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SwatchesLayeredNavigation\Block\Navigation\Category;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\LayeredNavigation\Block\Navigation\Category\AbstractFiltersTest;

/**
 * Provides tests for custom text swatch filter in navigation block on category page.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class SwatchVisualFilterTest extends AbstractFiltersTest
{
    /**
     * @magentoDataFixture Magento/Swatches/_files/product_visual_swatch_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/category_with_different_price_products.php
     * @dataProvider getFiltersWithCustomAttributeDataProvider
     * @param array $products
     * @param int $filterable
     * @param array $expectation
     * @return void
     */
    public function testGetFiltersWithCustomAttribute(array $products, int $filterable, array $expectation): void
    {
        $this->updateAttributeAndProducts('visual_swatch_attribute', $filterable, $products);
        $this->prepareNavigationBlock('Category 999');
        $filter = $this->getFilterByCode($this->navigationBlock->getFilters(), 'visual_swatch_attribute');

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
                    'simple1000' => 'option 1',
                    'simple1001' => 'option 2',
                ],
                'filterable' => AbstractFilter::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS,
                'expectation' => [
                    ['label' => 'option 1', 'count' => 1],
                    ['label' => 'option 2', 'count' => 1],
                ],
            ],
            'used_in_navigation_without_results' => [
                'products_data' => [
                    'simple1000' => 'option 1',
                    'simple1001' => 'option 2',
                ],
                'filterable' => 2,
                'expectation' => [
                    ['label' => 'option 1', 'count' => 1],
                    ['label' => 'option 2', 'count' => 1],
                    ['label' => 'option 3', 'count' => 0],
                ],
            ],
        ];
    }
}
