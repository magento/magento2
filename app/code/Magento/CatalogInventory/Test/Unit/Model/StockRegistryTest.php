<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model;

use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class StockRegistryTest
 */
class StockRegistryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    protected $model;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemCriteriaInterface|MockObject
     */
    protected $criteria;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory|MockObject
     */
    private $criteriaFactory;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterface|MockObject
     */
    private $stockItemMock;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface|MockObject
     */
    private $stockConfigurationMock;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface|MockObject
     */
    private $stockItemRepositoryMock;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface|MockObject
     */
    private $stockRegistryProviderMock;

    /**
     * @var \Magento\Catalog\Model\ProductFactory|MockObject
     */
    private $productFactoryMock;

    protected function setUp()
    {
        $this->criteria = $this->getMockBuilder(\Magento\CatalogInventory\Api\StockItemCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->criteriaFactory = $this->getMockBuilder(
            \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory::class
        )->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockItemMock = $this->getMockBuilder(\Magento\CatalogInventory\Api\Data\StockItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getWebsiteId',
                'getData',
                'addData',
                'getManageStock',
                'getIsInStock',
                'getQty',
                'getOrigData',
                'setIsInStock',
                'setStockStatusChangedAutomaticallyFlag',
            ])
            ->getMockForAbstractClass();

        $this->stockConfigurationMock = $this->createMock(
            \Magento\CatalogInventory\Api\StockConfigurationInterface::class
        );
        $this->stockRegistryProviderMock = $this->createMock(
            \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface::class
        );
        $this->stockItemRepositoryMock = $this->createMock(
            \Magento\CatalogInventory\Api\StockItemRepositoryInterface::class
        );
        $this->productFactoryMock = $this->createPartialMock(\Magento\Catalog\Model\ProductFactory::class, ['create']);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\CatalogInventory\Model\StockRegistry::class,
            [
                'stockConfiguration' => $this->stockConfigurationMock,
                'stockRegistryProvider' => $this->stockRegistryProviderMock,
                'stockItemRepository' => $this->stockItemRepositoryMock,
                'criteriaFactory' => $this->criteriaFactory,
                'productFactory' => $this->productFactoryMock,
            ]
        );
    }

    public function testGetLowStockItems()
    {
        $this->criteriaFactory->expects($this->once())->method('create')->willReturn($this->criteria);
        $this->criteria->expects($this->once())->method('setLimit')->with(1, 0);
        $this->criteria->expects($this->once())->method('setScopeFilter')->with(1);
        $this->criteria->expects($this->once())->method('setQtyFilter')->with('<=');
        $this->criteria->expects($this->once())->method('addField')->with('qty');
        $this->model->getLowStockItems(1, 100);
    }

    /**
     * @return void
     */
    public function testUpdateStockItemBySku()
    {
        $manageStock = 1;
        $isInStock = 0;
        $qty = 10;
        $origQty = 0;

        $this->stockItemMock->expects($this->once())->method('getManageStock')->willReturn($manageStock);
        $this->stockItemMock->expects($this->once())->method('getIsInStock')->willReturn($isInStock);
        $this->stockItemMock->expects($this->once())->method('getQty')->willReturn($qty);
        $this->stockItemMock->expects($this->exactly(2))
            ->method('getOrigData')
            ->with(\Magento\CatalogInventory\Api\Data\StockItemInterface::QTY)
            ->willReturn($origQty);

        $this->stockItemMock->expects($this->once())->method('setIsInStock')->with(true)->willReturnSelf();
        $this->stockItemMock->expects($this->once())
            ->method('setStockStatusChangedAutomaticallyFlag')
            ->with(true)
            ->willReturnSelf();

        $this->configureAndCallUpdateStockItemBySku();
    }

    /**
     * @return void
     */
    public function testUpdateStockItemBySkuWithoutUpdateStockStatus()
    {
        $manageStock = 0;

        $this->stockItemMock->expects($this->once())->method('getManageStock')->willReturn($manageStock);
        $this->stockItemMock->expects($this->never())->method('getIsInStock');
        $this->stockItemMock->expects($this->never())->method('getQty');
        $this->stockItemMock->expects($this->never())
            ->method('getOrigData')
            ->with(\Magento\CatalogInventory\Api\Data\StockItemInterface::QTY);

        $this->stockItemMock->expects($this->never())->method('setIsInStock')->with(true);
        $this->stockItemMock->expects($this->never())->method('setStockStatusChangedAutomaticallyFlag')->with(true);

        $this->configureAndCallUpdateStockItemBySku();
    }

    /**
     * @return void
     */
    private function configureAndCallUpdateStockItemBySku()
    {
        $productId = 1;
        $productSku = 'Simple';
        $websiteId = 0;
        $scopeId = 1;
        $data = ['item_id' => 1, 'is_in_stock' => 1];

        /** @var \Magento\CatalogInventory\Api\Data\StockItemInterface|MockObject $origStockItemMock */
        $origStockItemMock = $this->getMockBuilder(\Magento\CatalogInventory\Api\Data\StockItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItemId', 'addData', 'setProductId'])
            ->getMockForAbstractClass();

        /** @var \Magento\Catalog\Model\Product|MockObject $productMock */
        $productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getIdBySku']);
        $this->productFactoryMock->expects($this->once())->method('create')->willReturn($productMock);
        $productMock->expects($this->once())->method('getIdBySku')->with($productSku)->willReturn($productId);

        $this->stockItemMock->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);
        $this->stockItemMock->expects($this->once())->method('getData')->willReturn($data);

        $origStockItemMock->expects($this->exactly(2))->method('getItemId')->willReturn(null);
        $origStockItemMock->expects($this->once())->method('addData')->with($data)->willReturnSelf();
        $origStockItemMock->expects($this->once())->method('setProductId')->with($productId)->willReturnSelf();

        $this->stockConfigurationMock->expects($this->once())->method('getDefaultScopeId')->willReturn($scopeId);
        $this->stockRegistryProviderMock->expects($this->once())
            ->method('getStockItem')
            ->with($productId, $scopeId)
            ->willReturn($origStockItemMock);

        $this->stockItemRepositoryMock->expects($this->once())
            ->method('save')
            ->with($origStockItemMock)
            ->willReturn($origStockItemMock);

        $this->model->updateStockItemBySku($productSku, $this->stockItemMock);
    }
}
