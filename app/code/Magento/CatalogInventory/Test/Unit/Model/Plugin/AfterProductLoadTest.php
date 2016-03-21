<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\CatalogInventory\Test\Unit\Model\Plugin;

class AfterProductLoadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Plugin\AfterProductLoad
     */
    protected $plugin;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Catalog\Api\Data\ProductExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productExtensionFactoryMock;

    /**
     * @var \Magento\Catalog\Api\Data\ProductExtension|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productExtensionMock;

    protected function setUp()
    {
        $stockRegistryMock = $this->getMock('\Magento\CatalogInventory\Api\StockRegistryInterface');
        $this->productExtensionFactoryMock = $this->getMockBuilder('\Magento\Catalog\Api\Data\ProductExtensionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new \Magento\CatalogInventory\Model\Plugin\AfterProductLoad(
            $stockRegistryMock,
            $this->productExtensionFactoryMock
        );

        $productId = 5494;
        $stockItemMock = $this->getMock('\Magento\CatalogInventory\Api\Data\StockItemInterface');

        $stockRegistryMock->expects($this->once())
            ->method('getStockItem')
            ->with($productId)
            ->willReturn($stockItemMock);

        $this->productExtensionMock = $this->getMockBuilder('\Magento\Catalog\Api\Data\ProductExtension')
            ->setMethods(['setStockItem'])
            ->getMock();
        $this->productExtensionMock->expects($this->once())
            ->method('setStockItem')
            ->with($stockItemMock)
            ->willReturnSelf();

        $this->productMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->productExtensionMock)
            ->willReturnSelf();
        $this->productMock->expects(($this->once()))
            ->method('getId')
            ->will($this->returnValue($productId));
    }

    public function testAfterLoad()
    {
        // test when extension attributes are not (yet) present in the product
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn(null);
        $this->productExtensionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->productExtensionMock);

        $this->assertEquals(
            $this->productMock,
            $this->plugin->afterLoad($this->productMock)
        );
    }

    public function testAfterLoadWithExistingExtensionAttributes()
    {
        // test when extension attributes already exist
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->productExtensionFactoryMock->expects($this->never())
            ->method('create');

        $this->assertEquals(
            $this->productMock,
            $this->plugin->afterLoad($this->productMock)
        );
    }
}
