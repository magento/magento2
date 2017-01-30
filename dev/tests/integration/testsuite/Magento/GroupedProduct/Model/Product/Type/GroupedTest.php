<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\Product\Type;

class GroupedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_productType;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_productType = $this->objectManager->get('Magento\Catalog\Model\Product\Type');
    }

    public function testFactory()
    {
        $product = new \Magento\Framework\DataObject();
        $product->setTypeId(\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE);
        $type = $this->_productType->factory($product);
        $this->assertInstanceOf('\Magento\GroupedProduct\Model\Product\Type\Grouped', $type);
    }

    /**
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     * @magentoAppArea frontend
     */
    public function testGetAssociatedProducts()
    {
        $productRepository = $this->objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $productRepository->get('grouped-product');
        $type = $product->getTypeInstance();
        $this->assertInstanceOf('\Magento\GroupedProduct\Model\Product\Type\Grouped', $type);

        $associatedProducts = $type->getAssociatedProducts($product);
        $this->assertCount(2, $associatedProducts);

        $this->assertProductInfo($associatedProducts[0]);
        $this->assertProductInfo($associatedProducts[1]);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     */
    private function assertProductInfo($product)
    {
        $data = [
            1 => [
                'sku' => 'simple',
                'name' => 'Simple Product',
                'price' => '10',
                'qty' => '1',
                'position' => '1'
            ],
            21 => [
                'sku' => 'virtual-product',
                'name' => 'Virtual Product',
                'price' => '10',
                'qty' => '2',
                'position' => '2'
            ]
        ];
        $productId = $product->getId();
        $this->assertEquals($data[$productId]['sku'], $product->getSku());
        $this->assertEquals($data[$productId]['name'], $product->getName());
        $this->assertEquals($data[$productId]['price'], $product->getPrice());
        $this->assertEquals($data[$productId]['qty'], $product->getQty());
        $this->assertEquals($data[$productId]['position'], $product->getPosition());
    }
}
