<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for simple products generation by saving a configurable product.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class VariationHandlerTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var VariationHandler
     */
    private $variationHandler;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->variationHandler = $this->objectManager->create(VariationHandler::class);
        $this->product = $this->productRepository->get('configurable');
        $this->stockRegistry = $this->objectManager->get(StockRegistryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @dataProvider generateSimpleProductsDataProvider
     * @param array $productsData
     * @return void
     */
    public function testGenerateSimpleProducts(array $productsData): void
    {
        $this->product->setImage('some_test_image.jpg')
            ->setSmallImage('some_test_image.jpg')
            ->setThumbnail('some_test_image.jpg')
            ->setSwatchImage('some_test_image.jpg')
            ->setNewVariationsAttributeSetId($this->product->getDefaultAttributeSetId());
        $generatedProducts = $this->variationHandler->generateSimpleProducts($this->product, $productsData);
        $this->assertCount(3, $generatedProducts);
        foreach ($generatedProducts as $productId) {
            $stockItem = $this->stockRegistry->getStockItem($productId);
            $product = $this->productRepository->getById($productId);
            $this->assertNotNull($product->getName());
            $this->assertNotNull($product->getSku());
            $this->assertNotNull($product->getPrice());
            $this->assertNotNull($product->getWeight());
            $this->assertEquals('1', $stockItem->getIsInStock());
            $this->assertNull($product->getImage());
            $this->assertNull($product->getSmallImage());
            $this->assertNull($product->getThumbnail());
            $this->assertNull($product->getSwatchImage());
        }
    }

    /**
     * @param array $productsData
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @dataProvider generateSimpleProductsWithPartialDataDataProvider
     * @return void
     */
    public function testGenerateSimpleProductsWithPartialData(array $productsData): void
    {
        $this->product->setNewVariationsAttributeSetId(4);
        $generatedProducts = $this->variationHandler->generateSimpleProducts($this->product, $productsData);
        $parentStockItem = $this->stockRegistry->getStockItem($this->product->getId());
        foreach ($generatedProducts as $productId) {
            $stockItem = $this->stockRegistry->getStockItem($productId);
            $this->assertEquals($parentStockItem->getManageStock(), $stockItem->getManageStock());
            $this->assertEquals('1', $stockItem->getIsInStock());
        }
    }

    /**
     * @return array
     */
    public function generateSimpleProductsDataProvider(): array
    {
        return [
            [
                [
                    [
                        'name' => '1-aaa',
                        'configurable_attribute' => '{"configurable_attribute":"25"}',
                        'price' => '3',
                        'sku' => '1-aaa',
                        'quantity_and_stock_status' => ['qty' => '5'],
                        'weight' => '6',
                    ],
                    [
                        'name' => '1-bbb',
                        'configurable_attribute' => '{"configurable_attribute":"24"}',
                        'price' => '3',
                        'sku' => '1-bbb',
                        'quantity_and_stock_status' => ['qty' => '5'],
                        'weight' => '6'
                    ],
                    [
                        'name' => '1-ccc',
                        'configurable_attribute' => '{"configurable_attribute":"23"}',
                        'price' => '3',
                        'sku' => '1-ccc',
                        'quantity_and_stock_status' => ['qty' => '5'],
                        'weight' => '6'
                    ],
                ],
            ]
        ];
    }

    /**
     * @return array
     */
    public function generateSimpleProductsWithPartialDataDataProvider(): array
    {
        return [
            [
                [
                    [
                        'name' => '1-aaa',
                        'configurable_attribute' => '{"configurable_attribute":"23"}',
                        'price' => '3',
                        'sku' => '1-aaa-1',
                        'quantity_and_stock_status' => ['qty' => ''],
                        'weight' => '6',
                    ],
                ],
            ]
        ];
    }
}
