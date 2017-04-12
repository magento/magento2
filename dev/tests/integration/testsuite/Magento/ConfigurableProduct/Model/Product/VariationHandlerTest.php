<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\ConfigurableProduct\Model\Product;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 */
class VariationHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\ConfigurableProduct\Model\Product\VariationHandler */
    private $_model;

    /** @var \Magento\Catalog\Model\Product */
    private $_product;

    /** @var \Magento\CatalogInventory\Api\StockRegistryInterface */
    private $stockRegistry;

    protected function setUp()
    {
        $this->_product = Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $this->_product->load(1);

        $this->_model = Bootstrap::getObjectManager()->create(
            \Magento\ConfigurableProduct\Model\Product\VariationHandler::class
        );
        // prevent fatal errors by assigning proper "singleton" of type instance to the product
        $this->_product->setTypeInstance($this->_model);
        $this->stockRegistry = Bootstrap::getObjectManager()->get(
            \Magento\CatalogInventory\Api\StockRegistryInterface::class
        );
    }

    /**
     * @param array $productsData
     * @dataProvider generateSimpleProductsDataProvider
     */
    public function testGenerateSimpleProducts($productsData)
    {
        $this->_product->setNewVariationsAttributeSetId(4);
        // Default attribute set id
        $generatedProducts = $this->_model->generateSimpleProducts($this->_product, $productsData);
        $this->assertEquals(3, count($generatedProducts));
        foreach ($generatedProducts as $productId) {
            $stockItem = $this->stockRegistry->getStockItem($productId);
            /** @var $product \Magento\Catalog\Model\Product */
            $product = Bootstrap::getObjectManager()->create(
                \Magento\Catalog\Model\Product::class
            );
            $product->load($productId);
            $this->assertNotNull($product->getName());
            $this->assertNotNull($product->getSku());
            $this->assertNotNull($product->getPrice());
            $this->assertNotNull($product->getWeight());
            $this->assertEquals('1', $stockItem->getIsInStock());
        }
    }

    /**
     * @param array $productsData
     * @dataProvider generateSimpleProductsWithPartialDataDataProvider
     * @magentoDbIsolation enabled
     */
    public function testGenerateSimpleProductsWithPartialData($productsData)
    {
        $this->_product->setNewVariationsAttributeSetId(4);
        $generatedProducts = $this->_model->generateSimpleProducts($this->_product, $productsData);
        $parentStockItem = $this->stockRegistry->getStockItem($this->_product->getId());
        foreach ($generatedProducts as $productId) {
            $stockItem = $this->stockRegistry->getStockItem($productId);
            $this->assertEquals($parentStockItem->getManageStock(), $stockItem->getManageStock());
            $this->assertEquals('1', $stockItem->getIsInStock());
        }
    }

    /**
     * @return array
     */
    public static function generateSimpleProductsDataProvider()
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
    public static function generateSimpleProductsWithPartialDataDataProvider()
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
