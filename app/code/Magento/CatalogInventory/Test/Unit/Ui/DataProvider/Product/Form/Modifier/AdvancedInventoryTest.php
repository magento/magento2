<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Source\Stock;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Store\Model\Store;
use Magento\CatalogInventory\Ui\DataProvider\Product\Form\Modifier\AdvancedInventory;

/**
 * Class AdvancedInventoryTest
 */
class AdvancedInventoryTest extends AbstractModifierTest
{
    /**
     * @var Stock|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockMock;

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
        $this->stockMock = $this->getMockBuilder(Stock::class)
            ->disableOriginalConstructor()
            ->getMock();
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
            'stockRegistry' => $this->stockRegistryMock,
            'stock' => $this->stockMock,
        ]);
    }

    public function testModifyMeta()
    {
        $this->assertNotEmpty($this->getModel()->modifyMeta(['meta_key' => 'meta_value']));
    }

    public function testModifyData()
    {
        $modelId = 1;

        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($modelId);
        $this->stockItemMock->expects($this->once())
            ->method('getData')
            ->willReturn($this->getSampleData());

        $this->assertArrayHasKey($modelId, $this->getModel()->modifyData([]));
    }
}
