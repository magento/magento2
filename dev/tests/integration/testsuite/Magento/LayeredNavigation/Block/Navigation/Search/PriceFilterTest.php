<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation\Search;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\LayeredNavigation\Block\Navigation\Category\PriceFilterTest as CategoryPriceFilterTest;

/**
 * Provides price filter tests with different price ranges calculation in navigation block on search page.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class PriceFilterTest extends CategoryPriceFilterTest
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/category_with_three_products.php
     * @dataProvider getFiltersDataProvider
     * @param array $config
     * @param array $products
     * @param array $expectation
     * @return void
     */
    public function testGetFilters(array $config, array $products, array $expectation): void
    {
        $this->applyCatalogConfig($config);
        $this->getSearchFiltersAndAssert(
            $products,
            [
                'is_filterable' => AbstractFilter::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS,
                'is_filterable_in_search' => 1,
            ],
            $expectation
        );
    }

    /**
     * @inheritdoc
     */
    protected function getLayerType(): string
    {
        return Resolver::CATALOG_LAYER_SEARCH;
    }
}
