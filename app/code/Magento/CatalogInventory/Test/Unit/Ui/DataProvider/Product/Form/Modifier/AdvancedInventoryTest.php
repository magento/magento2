<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Store\Model\Store;
use Magento\CatalogInventory\Ui\DataProvider\Product\Form\Modifier\AdvancedInventory;

/**
 * Class AdvancedInventoryTest
 */
class AdvancedInventoryTest extends AbstractModifierTest
{
    /**
     * @var StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistryMock;

    /**
     * @var StockItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemMock;

    /**
     * @var Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    protected function setUp()
    {
        parent::setUp();
        $this->stockRegistryMock = $this->getMockBuilder(StockRegistryInterface::class)
            ->setMethods(['getStockItem'])
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemMock = $this->getMockBuilder(StockItemInterface::class)
            ->setMethods(['getData'])
            ->getMockForAbstractClass();

        $this->stockRegistryMock->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
        $this->productMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(AdvancedInventory::class, [
            'locator' => $this->locatorMock,
            'stockRegistry' => $this->stockRegistryMock
        ]);
    }

    public function testModifyMeta()
    {
        $this->assertNotEmpty($this->getModel()->modifyMeta(['meta_key' => 'meta_value']));
    }

    public function testModifyData()
    {
        $modelId = 1;
        $someData = 1;

        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($modelId);
        $this->stockItemMock->expects($this->once())->method('getData')->willReturn(['someData']);
        $this->stockItemMock->expects($this->once())->method('getManageStock')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getQty')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getMinQty')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getMinSaleQty')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getMaxSaleQty')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getIsQtyDecimal')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getIsDecimalDivided')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getBackorders')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getNotifyStockQty')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getEnableQtyIncrements')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getQtyIncrements')->willReturn($someData);
        $this->stockItemMock->expects($this->once())->method('getIsInStock')->willReturn($someData);

        $this->assertArrayHasKey($modelId, $this->getModel()->modifyData([]));
    }
}
