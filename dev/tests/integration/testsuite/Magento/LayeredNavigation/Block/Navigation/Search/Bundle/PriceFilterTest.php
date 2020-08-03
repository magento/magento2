<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation\Search\Bundle;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\LayeredNavigation\Block\Navigation\Category\Bundle\PriceFilterTest as CategoryFilterTest;

/**
 * Provides price filter tests for bundle product in navigation block on search page.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class PriceFilterTest extends CategoryFilterTest
{
    /**
     * @magentoDataFixture Magento/Bundle/_files/dynamic_and_fixed_bundle_products_in_category.php
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_calculation manual
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_step 10
     * @return void
     */
    public function testGetFilters(): void
    {
        $this->getSearchFiltersAndAssert(
            ['bundle-product' => 20.00],
            ['is_filterable_in_search' => 1],
            [
                ['label' => '$10.00 - $19.99', 'value' => '10-20', 'count' => 1],
                ['label' => '$20.00 and above', 'value' => '20-30', 'count' => 1],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected function getLayerType(): string
    {
        return Resolver::CATALOG_LAYER_SEARCH;
    }

    /**
     * @inheritdoc
     */
    protected function getSearchString(): string
    {
        return 'Bundle';
    }
}
