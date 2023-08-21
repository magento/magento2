<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block\Navigation\Category\Configurable;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Module\Manager;
use Magento\LayeredNavigation\Block\Navigation\AbstractFiltersTest;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Store\Model\Store;

/**
 * Provides price filter tests for configurable in navigation block on category page.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class PriceFilterTest extends AbstractFiltersTest
{
    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->moduleManager = $this->objectManager->get(Manager::class);
        //This check is needed because LayeredNavigation independent of Magento_ConfigurableProduct
        if (!$this->moduleManager->isEnabled('Magento_ConfigurableProduct')) {
            $this->markTestSkipped('Magento_ConfigurableProduct module disabled.');
        }
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_category.php
     * @magentoDataFixture Magento/Catalog/_files/category_product.php
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_calculation manual
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_step 10
     * @dataProvider getFiltersDataProvider
     * @param array $products
     * @param array $expectation
     * @return void
     */
    public function testGetFilters(array $products, array $expectation): void
    {
        $this->updateProductData($products);
        $this->getCategoryFiltersAndAssert([], ['is_filterable' => '1'], $expectation, 'Category 1');
    }

    /**
     * @return array
     */
    public function getFiltersDataProvider(): array
    {
        return [
            'all_children_active' => [
                'products_data' => [
                    'simple333' => ['price' => 60.00],
                ],
                'expectation' => [
                    [
                        'label' => '<span class="price">$10.00</span> - <span class="price">$19.99</span>',
                        'value' => '10-20',
                        'count' => 1,
                    ],
                    [
                        'label' => '<span class="price">$60.00</span> and above',
                        'value' => '60-70',
                        'count' => 1,
                    ],
                ],
            ],
            'one_child_disabled' => [
                'products_data' => [
                    'simple333' => ['price' => 50.00],
                    'simple_10' => ['status' => Status::STATUS_DISABLED],
                ],
                'expectation' => [
                    [
                        'label' => '<span class="price">$20.00</span> - <span class="price">$29.99</span>',
                        'value' => '20-30',
                        'count' => 1,
                    ],
                    [
                        'label' => '<span class="price">$50.00</span> and above',
                        'value' => '50-60',
                        'count' => 1,
                    ],
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
     * Updates products data.
     *
     * @param array $products
     * @param int $storeId
     * @return void
     */
    private function updateProductData(
        array $products,
        int $storeId = Store::DEFAULT_STORE_ID
    ): void {
        foreach ($products as $productSku => $data) {
            $product = $this->productRepository->get($productSku, false, $storeId, true);
            $product->addData($data);
            $this->productRepository->save($product);
        }
    }
}
