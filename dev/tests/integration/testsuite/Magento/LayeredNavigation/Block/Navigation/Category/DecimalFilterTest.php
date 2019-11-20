<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation\Category;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Store\Model\Store;

/**
 * Provides tests for custom price filter in navigation block on category page.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class DecimalFilterTest extends AbstractFiltersTest
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_decimal_attribute.php
     * @magentoDataFixture Magento/Catalog/_files/category_with_different_price_products.php
     * @dataProvider getFiltersWithCustomAttributeDataProvider
     * @param array $products
     * @param int $filterable
     * @param array $expectation
     * @return void
     */
    public function testGetFiltersWithCustomAttribute(array $products, int $filterable, array $expectation): void
    {
        $this->getFiltersAndAssert($products, $filterable, $expectation, 'decimal_attribute');
    }

    /**
     * @inheritdoc
     */
    protected function prepareFilterItems(AbstractFilter $filter): array
    {
        $items = [];
        /** @var Item $item */
        foreach ($filter->getItems() as $item) {
            $item = [
                'label' => __($item->getData('label'))->render(),
                'value' => $item->getData('value'),
                'count' => $item->getData('count'),
            ];
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @inheritdoc
     */
    protected function updateProducts(array $products, string $attributeCode): void
    {
        $attribute = $this->attributeRepository->get($attributeCode);

        foreach ($products as $productSku => $value) {
            $product = $this->productRepository->get($productSku, false, Store::DEFAULT_STORE_ID, true);
            $product->addData(
                [$attribute->getAttributeCode() => $value]
            );
            $this->productRepository->save($product);
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
                    'simple1000' => 10.00,
                    'simple1001' => 20.00,
                ],
                'filterable' => AbstractFilter::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS,
                'expectation' => [
                    [
                        'label' => '<span class="price">$10.00</span> - <span class="price">$19.99</span>',
                        'value' => '10-20',
                        'count' => 1,
                    ],
                    [
                        'label' => '<span class="price">$20.00</span> and above',
                        'value' => '20-',
                        'count' => 1,
                    ],
                ],
            ],
        ];
    }
}
