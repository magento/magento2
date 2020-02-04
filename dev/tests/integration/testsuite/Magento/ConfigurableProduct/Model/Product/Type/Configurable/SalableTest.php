<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Check is configurable product salable with different conditions
 *
 * @magentoAppArea frontend
 */
class SalableTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     *
     * @dataProvider salableDataProvider
     *
     * @param array $productSkus
     * @param array $productData
     * @param bool $expectedValue
     * @return void
     */
    public function testIsSalable(array $productSkus, array $productData, bool $expectedValue): void
    {
        $this->updateProduct($productSkus, $productData);
        $configurableProduct = $this->productRepository->get('configurable', false, null, true);

        $this->assertEquals($expectedValue, $configurableProduct->getIsSalable());
    }

    /**
     * @return array
     */
    public function salableDataProvider(): array
    {
        return [
            'all children enabled_and_in_stock' => [
                'product_skus' => [],
                'data' => [],
                'expected_value' => true,
            ],
            'one_child_out_of_stock' => [
                'product_skus' => ['simple_10'],
                'data' => [
                    'stock_data' => [
                        'use_config_manage_stock' => 1,
                        'is_in_stock' => StockStatusInterface::STATUS_OUT_OF_STOCK,
                    ],
                ],
                'expected_value' => true,
            ],
            'one_child_disabled' => [
                'product_skus' => ['simple_10'],
                'data' => ['status' => Status::STATUS_DISABLED],
                'expected_value' => true,
            ],
            'all_children_disabled' => [
                'product_skus' => ['simple_10', 'simple_20'],
                'data' => ['status' => Status::STATUS_DISABLED],
                'expected_value' => false,
            ],
            'all_children_out_of_stock' => [
                'product_skus' => ['simple_10', 'simple_20'],
                'data' => [
                    'stock_data' => [
                        'use_config_manage_stock' => 1,
                        'is_in_stock' => StockStatusInterface::STATUS_OUT_OF_STOCK,
                    ],
                ],
                'expected_value' => false,
            ]
        ];
    }

    /**
     * Update product with data
     *
     * @param array $skus
     * @param array $data
     * @return void
     */
    private function updateProduct(array $skus, array $data): void
    {
        if (!empty($skus)) {
            foreach ($skus as $sku) {
                $product = $this->productRepository->get($sku);
                $product->addData($data);
                $this->productRepository->save($product);
            }
        }
    }
}
