<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation\Search;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\LayeredNavigation\Block\Navigation\Category\MultipleFiltersTest as CategoryFilterTest;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

/**
 * Provides tests for multiple custom select filters in navigation block on search page.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class MultipleFiltersTest extends CategoryFilterTest
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_dropdown_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/configurable_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/category_with_three_products.php
     * @dataProvider getMultipleActiveFiltersDataProvider
     * @param array $products
     * @param array $filters
     * @param array $expectedProducts
     * @return void
     */
    public function testGetMultipleActiveFilters(
        array $products,
        array $filters,
        array $expectedProducts
    ): void {
        $this->updateAttributesAndProducts(
            $products,
            ['is_filterable' => AbstractFilter::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS, 'is_filterable_in_search' => 1]
        );
        $this->clearInstanceAndReindexSearch();
        $this->navigationBlock->getRequest()->setParams(
            array_merge($this->getMultipleRequestParams($filters), ['q' => $this->getSearchString()])
        );
        $this->navigationBlock->setLayout($this->layout);
        $resultProducts = $this->getProductSkus($this->navigationBlock->getLayer()->getProductCollection());
        $this->assertEquals($expectedProducts, $resultProducts);
    }

    /**
     * @inheritdoc
     */
    protected function getLayerType(): string
    {
        return Resolver::CATALOG_LAYER_SEARCH;
    }
}
