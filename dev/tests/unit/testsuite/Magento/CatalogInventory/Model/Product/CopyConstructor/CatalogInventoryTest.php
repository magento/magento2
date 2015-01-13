<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Product\CopyConstructor;

class CatalogInventoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Product\CopyConstructor\CatalogInventory
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $duplicateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemDoMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    protected function setUp()
    {
        $this->productMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            ['__wakeup', 'getStore'],
            [],
            '',
            false
        );
        $store = $this->getMock(
            '\Magento\Store\Model\Store',
            ['getWebsiteId', '__wakeup'],
            [],
            '',
            false
        );
        $store->expects($this->any())->method('getWebsiteId')->willReturn(0);
        $this->productMock->expects($this->any())->method('getStore')->willReturn($store);

        $this->duplicateMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            ['setStockData', '__wakeup'],
            [],
            '',
            false
        );

        $this->stockItemDoMock = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\Data\StockItemInterface',
            [
                'getItemId',
                'getUseConfigEnableQtyInc',
                'getEnableQtyIncrements',
                'gerUseConfigQtyIncrements',
                'getQtyIncrements'
            ]
        );

        $this->stockRegistry = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\StockRegistryInterface',
            ['getStockItem']
        );

        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            'Magento\CatalogInventory\Model\Product\CopyConstructor\CatalogInventory',
            ['stockRegistry' => $this->stockRegistry]
        );
    }

    public function testBuildWithoutCurrentProductStockItem()
    {
        $expectedData = [
            'use_config_min_qty' => 1,
            'use_config_min_sale_qty' => 1,
            'use_config_max_sale_qty' => 1,
            'use_config_backorders' => 1,
            'use_config_notify_stock_qty' => 1,
        ];
        $this->stockItemDoMock->expects($this->any())->method('getStockId')->will($this->returnValue(false));

        $this->stockRegistry->expects($this->once())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemDoMock));

        $this->duplicateMock->expects($this->once())->method('setStockData')->with($expectedData);
        $this->model->build($this->productMock, $this->duplicateMock);
    }

    public function testBuildWithCurrentProductStockItem()
    {
        $expectedData = [
            'use_config_min_qty' => 1,
            'use_config_min_sale_qty' => 1,
            'use_config_max_sale_qty' => 1,
            'use_config_backorders' => 1,
            'use_config_notify_stock_qty' => 1,
            'use_config_enable_qty_inc' => 'use_config_enable_qty_inc',
            'enable_qty_increments' => 'enable_qty_increments',
            'use_config_qty_increments' => 'use_config_qty_increments',
            'qty_increments' => 'qty_increments',
        ];
        $this->stockRegistry->expects($this->once())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemDoMock));

        $this->stockItemDoMock->expects($this->any())->method('getItemId')->will($this->returnValue(50));
        $this->stockItemDoMock->expects($this->any())
            ->method('getUseConfigEnableQtyInc')
            ->will($this->returnValue('use_config_enable_qty_inc'));
        $this->stockItemDoMock->expects($this->any())
            ->method('getEnableQtyIncrements')
            ->will($this->returnValue('enable_qty_increments'));
        $this->stockItemDoMock->expects($this->any())
            ->method('getUseConfigQtyIncrements')
            ->will($this->returnValue('use_config_qty_increments'));
        $this->stockItemDoMock->expects($this->any())
            ->method('getQtyIncrements')
            ->will($this->returnValue('qty_increments'));

        $this->duplicateMock->expects($this->once())->method('setStockData')->with($expectedData);
        $this->model->build($this->productMock, $this->duplicateMock);
    }
}
