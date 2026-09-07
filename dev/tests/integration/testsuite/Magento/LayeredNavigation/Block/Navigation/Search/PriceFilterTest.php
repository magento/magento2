<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation\Search;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\LayeredNavigation\Block\Navigation\Category\PriceFilterTest as CategoryPriceFilterTest;
use PHPUnit\Framework\Attributes\DataProvider;

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
     * @param array $config
     * @param array $products
     * @param array $expectation
     * @return void
     */
    #[DataProvider('getFiltersDataProvider')]
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
     * @magentoDataFixture Magento/Catalog/_files/category_with_three_products.php
     * @param array $config
     * @param array $products
     * @param array $expectation
     * @param string $filterValue
     * @return void
     */
    #[DataProvider('getActiveFiltersDataProvider')]
    public function testGetActiveFilters(array $config, array $products, array $expectation, string $filterValue): void
    {
        $this->applyCatalogConfig($config);
        $this->getSearchActiveFiltersAndAssert($products, $expectation, $filterValue, 1);
    }

    /**
     * @inheritdoc
     */
    protected function getLayerType(): string
    {
        return Resolver::CATALOG_LAYER_SEARCH;
    }
}
