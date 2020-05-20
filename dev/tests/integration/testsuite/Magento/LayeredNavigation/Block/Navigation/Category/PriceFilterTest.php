<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation\Category;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\ScopeInterface;
use Magento\LayeredNavigation\Block\Navigation\AbstractFiltersTest;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Store\Model\ScopeInterface as StoreScope;
use Magento\Store\Model\Store;

/**
 * Provides price filter tests with different price ranges calculation in navigation block on category page.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class PriceFilterTest extends AbstractFiltersTest
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
        $this->getCategoryFiltersAndAssert(
            $products,
            ['is_filterable' => AbstractFilter::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS],
            $expectation,
            'Category 999'
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function getFiltersDataProvider(): array
    {
        return [
            'auto_calculation_variation_with_small_price_difference' => [
                'config' => ['catalog/layered_navigation/price_range_calculation' => 'auto'],
                'products_data' => ['simple1000' => 10.00, 'simple1001' => 20.00, 'simple1002' => 50.00],
                'expectation' => [
                    ['label' => '$10.00 - $19.99', 'value' => '10-20', 'count' => 1],
                    ['label' => '$20.00 - $29.99', 'value' => '20-30', 'count' => 1],
                    ['label' => '$50.00 and above', 'value' => '50-', 'count' => 1],
                ],
            ],
            'auto_calculation_variation_with_big_price_difference' => [
                'config' => ['catalog/layered_navigation/price_range_calculation' => 'auto'],
                'products_data' => ['simple1000' => 10.00, 'simple1001' => 20.00, 'simple1002' => 300.00],
                'expectation' => [
                    ['label' => '$0.00 - $99.99', 'value' => '-100', 'count' => 2],
                    ['label' => '$300.00 and above', 'value' => '300-', 'count' => 1],
                ],
            ],
            'auto_calculation_variation_with_fixed_price_step' => [
                'config' => ['catalog/layered_navigation/price_range_calculation' => 'auto'],
                'products_data' => ['simple1000' => 300.00, 'simple1001' => 400.00, 'simple1002' => 500.00],
                'expectation' => [
                    ['label' => '$300.00 - $399.99', 'value' => '300-400', 'count' => 1],
                    ['label' => '$400.00 - $499.99', 'value' => '400-500', 'count' => 1],
                    ['label' => '$500.00 and above', 'value' => '500-', 'count' => 1],
                ],
            ],
            'improved_calculation_variation_with_small_price_difference' => [
                'config' => [
                    'catalog/layered_navigation/price_range_calculation' => 'improved',
                    'catalog/layered_navigation/interval_division_limit' => 3,
                ],
                'products_data' => ['simple1000' => 10.00, 'simple1001' => 20.00, 'simple1002' => 50.00],
                'expectation' => [
                    ['label' => '$0.00 - $49.99', 'value' => '-50', 'count' => 2],
                    ['label' => '$50.00 and above', 'value' => '50-', 'count' => 1],
                ],
            ],
            'improved_calculation_variation_with_big_price_difference' => [
                'config' => [
                    'catalog/layered_navigation/price_range_calculation' => 'improved',
                    'catalog/layered_navigation/interval_division_limit' => 3,
                ],
                'products_data' => ['simple1000' => 10.00, 'simple1001' => 20.00, 'simple1002' => 300.00],
                'expectation' => [
                    ['label' => '$0.00 - $299.99', 'value' => '-300', 'count' => 2.0],
                    ['label' => '$300.00 and above', 'value' => '300-', 'count' => 1.0],
                ],
            ],
            'manual_calculation_with_price_step_200' => [
                'config' => [
                    'catalog/layered_navigation/price_range_calculation' => 'manual',
                    'catalog/layered_navigation/price_range_step' => 200,
                ],
                'products_data' => ['simple1000' => 300.00, 'simple1001' => 300.00, 'simple1002' => 500.00],
                'expectation' => [
                    ['label' => '$200.00 - $399.99', 'value' => '200-400', 'count' => 2],
                    ['label' => '$400.00 and above', 'value' => '400-', 'count' => 1],
                ],
            ],
            'manual_calculation_with_price_step_10' => [
                'config' => [
                    'catalog/layered_navigation/price_range_calculation' => 'manual',
                    'catalog/layered_navigation/price_range_step' => 10,
                ],
                'products_data' => ['simple1000' => 300.00, 'simple1001' => 300.00, 'simple1002' => 500.00],
                'expectation' => [
                    ['label' => '$300.00 - $309.99', 'value' => '300-310', 'count' => 2],
                    ['label' => '$500.00 and above', 'value' => '500-', 'count' => 1],
                ],
            ],
            'manual_calculation_with_number_of_intervals_10' => [
                'config' => [
                    'catalog/layered_navigation/price_range_calculation' => 'manual',
                    'catalog/layered_navigation/price_range_step' => 10,
                    'catalog/layered_navigation/price_range_max_intervals' => 10,
                ],
                'products_data' => ['simple1000' => 10.00, 'simple1001' => 20.00, 'simple1002' => 30.00],
                'expectation' => [
                    ['label' => '$10.00 - $19.99', 'value' => '10-20', 'count' => 1],
                    ['label' => '$20.00 - $29.99', 'value' => '20-30', 'count' => 1],
                    ['label' => '$30.00 and above', 'value' => '30-', 'count' => 1],
                ],
            ],
            'manual_calculation_with_number_of_intervals_2' => [
                'config' => [
                    'catalog/layered_navigation/price_range_calculation' => 'manual',
                    'catalog/layered_navigation/price_range_step' => 10,
                    'catalog/layered_navigation/price_range_max_intervals' => 2,
                ],
                'products_data' => ['simple1000' => 10.00, 'simple1001' => 20.00, 'simple1002' => 30.00],
                'expectation' => [
                    ['label' => '$10.00 - $19.99', 'value' => '10-20', 'count' => 1],
                    ['label' => '$20.00 and above', 'value' => '20-', 'count' => 2],
                ],
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
        return 'price';
    }

    /**
     * @inheritdoc
     */
    protected function prepareFilterItems(AbstractFilter $filter): array
    {
        $items = [];
        /** @var Item $item */
        foreach ($filter->getItems() as $item) {
            $items[] = [
                'label' => strip_tags(__($item->getData('label'))->render()),
                'value' => $item->getData('value'),
                'count' => $item->getData('count'),
            ];
        }

        return $items;
    }

    /**
     * Updates price filter store configuration.
     *
     * @param array $config
     * @return void
     */
    protected function applyCatalogConfig(array $config): void
    {
        foreach ($config as $path => $value) {
            $this->scopeConfig->setValue($path, $value, StoreScope::SCOPE_STORE, ScopeInterface::SCOPE_DEFAULT);
        }
    }
}
