<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation\Category;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\LayeredNavigation\Block\Navigation\AbstractFiltersTest;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Store\Model\Store;

/**
 * Provides tests for multiple custom select filters in navigation block on category page.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class MultipleFiltersTest extends AbstractFiltersTest
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
            ['is_filterable' => AbstractFilter::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS]
        );
        $this->clearInstanceAndReindexSearch();
        $this->navigationBlock->getRequest()->setParams($this->getMultipleRequestParams($filters));
        $this->navigationBlock->getLayer()->setCurrentCategory(
            $this->loadCategory('Category 999', Store::DEFAULT_STORE_ID)
        );
        $this->navigationBlock->setLayout($this->layout);
        $resultProducts = $this->getProductSkus($this->navigationBlock->getLayer()->getProductCollection());
        self::assertEqualsCanonicalizing($expectedProducts, $resultProducts);
    }

    /**
     * @return array
     */
    public function getMultipleActiveFiltersDataProvider(): array
    {
        return [
            'without_filters' => [
                'products_data' => [
                    'test_configurable' => [
                        'simple1000' => 'Option 1',
                        'simple1001' => 'Option 2',
                        'simple1002' => 'Option 2',
                    ],
                    'dropdown_attribute' => [
                        'simple1000' => 'Option 1',
                        'simple1001' => 'Option 2',
                        'simple1002' => 'Option 3',
                    ],
                ],
                'filters' => [],
                'expected_products' => ['simple1000', 'simple1001', 'simple1002'],
            ],
            'applied_first_option_in_both_filters' => [
                'products_data' => [
                    'test_configurable' => [
                        'simple1000' => 'Option 1',
                        'simple1001' => 'Option 1',
                        'simple1002' => 'Option 2',
                    ],
                    'dropdown_attribute' => [
                        'simple1000' => 'Option 1',
                        'simple1001' => 'Option 1',
                        'simple1002' => 'Option 3',
                    ],
                ],
                'filters' => ['test_configurable' => 'Option 1', 'dropdown_attribute' => 'Option 1'],
                'expected_products' => ['simple1000', 'simple1001'],
            ],
            'applied_mixed_options_in_filters' => [
                'products_data' => [
                    'test_configurable' => [
                        'simple1000' => 'Option 1',
                        'simple1001' => 'Option 2',
                        'simple1002' => 'Option 2',
                    ],
                    'dropdown_attribute' => [
                        'simple1000' => 'Option 1',
                        'simple1001' => 'Option 2',
                        'simple1002' => 'Option 3',
                    ],
                ],
                'filters' => ['test_configurable' => 'Option 2', 'dropdown_attribute' => 'Option 3'],
                'expected_products' => ['simple1002'],
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
     * Updates products and product attribute.
     *
     * @param array $productsData
     * @param array $attributesData
     * @return void
     */
    protected function updateAttributesAndProducts(array $productsData, array $attributesData): void
    {
        $products = [];
        foreach ($productsData as $attributeCode => $data) {
            $this->updateAttribute($attributesData, $attributeCode);
            $attribute = $this->attributeRepository->get($attributeCode);

            foreach ($data as $productSku => $stringValue) {
                if (empty($products[$productSku])) {
                    $product = $this->productRepository->get($productSku, false, Store::DEFAULT_STORE_ID, true);
                    $products[$productSku] = $product;
                } else {
                    $product = $products[$productSku];
                }
                $productValue = $attribute->usesSource()
                    ? $attribute->getSource()->getOptionId($stringValue)
                    : $stringValue;
                $product->addData([$attribute->getAttributeCode() => $productValue]);
            }
        }
        foreach ($products as $product) {
            $this->productRepository->save($product);
        }
    }

    /**
     * Returns array with multiple filters.
     *
     * @param array $filters
     * @return array
     */
    protected function getMultipleRequestParams(array $filters): array
    {
        $params = [];
        foreach ($filters as $attributeCode => $filterValue) {
            $attribute = $this->attributeRepository->get($attributeCode);
            $filterValue = $attribute->usesSource()
                ? $attribute->getSource()->getOptionId($filterValue)
                : $filterValue;

            $params[$attributeCode] = $filterValue;
        }

        return $params;
    }

    /**
     * Returns list of product skus from given collection.
     *
     * @param Collection $getProductCollection
     * @return array
     */
    protected function getProductSkus(Collection $getProductCollection): array
    {
        $skus = [];
        foreach ($getProductCollection as $product) {
            $skus[] = $product->getSku();
        }

        return $skus;
    }
}
