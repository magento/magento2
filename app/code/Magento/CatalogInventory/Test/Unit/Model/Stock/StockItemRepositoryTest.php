<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\Stock;

use \Magento\CatalogInventory\Model\Stock\StockItemRepository;
use \Magento\CatalogInventory\Api\Data as InventoryApiData;

/**
 * Class StockItemRepositoryTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockItemRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StockItemRepository
     */
    protected $model;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\Item |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemMock;
    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockConfigurationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStateProviderMock;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemResourceMock;

    /**
     * @var InventoryApiData\StockItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemFactoryMock;

    /**
     * @var InventoryApiData\StockItemCollectionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemCollectionMock;

    /**
     * @var \Magento\Catalog\Model\ProductFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactoryMock;

    /**
     * @var \Magento\Framework\DB\QueryBuilderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $queryBuilderFactoryMock;

    /**
     * @var \Magento\Framework\DB\MapperFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapperMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDateMock;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexProcessorMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateTime;

    protected function setUp()
    {
        $this->stockItemMock = $this->getMockBuilder('\Magento\CatalogInventory\Model\Stock\Item')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getItemId',
                    'getProductId',
                    'setIsInStock',
                    'setStockStatusChangedAutomaticallyFlag',
                    'getStockStatusChangedAutomaticallyFlag',
                    'getManageStock',
                    'setLowStockDate',
                    'setStockStatusChangedAuto',
                    'hasStockStatusChangedAutomaticallyFlag',
                    'setQty',
                    'getWebsiteId',
                    'setWebsiteId',
                    'getStockId',
                    'setStockId'
                ]
            )
            ->getMock();
        $this->stockConfigurationMock = $this->getMockBuilder(
            'Magento\CatalogInventory\Api\StockConfigurationInterface'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockStateProviderMock = $this->getMockBuilder(
            'Magento\CatalogInventory\Model\Spi\StockStateProviderInterface'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemResourceMock = $this->getMockBuilder('Magento\CatalogInventory\Model\ResourceModel\Stock\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemFactoryMock = $this->getMockBuilder(
            'Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockItemCollectionMock = $this->getMockBuilder(
            'Magento\CatalogInventory\Api\Data\StockItemCollectionInterfaceFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productFactoryMock = $this->getMockBuilder('Magento\Catalog\Model\ProductFactory')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'create'])
            ->getMock();
        $this->productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'getTypeId', '__wakeup'])
            ->getMock();
        $this->productFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->productMock);

        $this->queryBuilderFactoryMock = $this->getMockBuilder('Magento\Framework\DB\QueryBuilderFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->mapperMock = $this->getMockBuilder('Magento\Framework\DB\MapperFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeDateMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->indexProcessorMock = $this->getMock(
            'Magento\CatalogInventory\Model\Indexer\Stock\Processor',
            ['reindexRow'],
            [],
            '',
            false
        );
        $this->dateTime = $this->getMock(
            'Magento\Framework\Stdlib\DateTime\DateTime',
            ['gmtDate'],
            [],
            '',
            false
        );

        $this->model = new StockItemRepository(
            $this->stockConfigurationMock,
            $this->stockStateProviderMock,
            $this->stockItemResourceMock,
            $this->stockItemFactoryMock,
            $this->stockItemCollectionMock,
            $this->productFactoryMock,
            $this->queryBuilderFactoryMock,
            $this->mapperMock,
            $this->localeDateMock,
            $this->indexProcessorMock,
            $this->dateTime
        );
    }

    public function testDelete()
    {
        $this->stockItemResourceMock->expects($this->once())
            ->method('delete')
            ->with($this->stockItemMock)
            ->willReturnSelf();

        $this->assertTrue($this->model->delete($this->stockItemMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function testDeleteException()
    {
        $this->stockItemResourceMock->expects($this->once())
            ->method('delete')
            ->with($this->stockItemMock)
            ->willThrowException(new \Exception());

        $this->model->delete($this->stockItemMock);
    }

    public function testDeleteById()
    {
        $id = 1;

        $this->stockItemFactoryMock->expects($this->once())->method('create')->willReturn($this->stockItemMock);
        $this->stockItemResourceMock->expects($this->once())->method('load')->with($this->stockItemMock, $id);
        $this->stockItemMock->expects($this->once())->method('getItemId')->willReturn($id);

        $this->assertTrue($this->model->deleteById($id));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage Stock Item with id "1" does not exist.
     */
    public function testDeleteByIdException()
    {
        $id = 1;

        $this->stockItemFactoryMock->expects($this->once())->method('create')->willReturn($this->stockItemMock);
        $this->stockItemResourceMock->expects($this->once())->method('load')->with($this->stockItemMock, $id);
        $this->stockItemMock->expects($this->once())->method('getItemId')->willReturn(null);

        $this->assertTrue($this->model->deleteById($id));
    }

    public function testSave()
    {
        $productId = 1;

        $this->stockItemMock->expects($this->any())->method('getProductId')->willReturn($productId);
        $this->productMock->expects($this->once())->method('load')->with($productId)->willReturnSelf();
        $this->productMock->expects($this->once())->method('getId')->willReturn($productId);
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('typeId');
        $this->stockConfigurationMock->expects($this->once())->method('isQty')->with('typeId')->willReturn(true);
        $this->stockStateProviderMock->expects($this->once())
            ->method('verifyStock')
            ->with($this->stockItemMock)
            ->willReturn(false);
        $this->stockItemMock->expects($this->once())->method('getManageStock')->willReturn(true);
        $this->stockItemMock->expects($this->once())->method('setIsInStock')->with(false)->willReturnSelf();
        $this->stockItemMock->expects($this->once())
            ->method('setStockStatusChangedAutomaticallyFlag')
            ->with(true)
            ->willReturnSelf();
        $this->stockItemMock->expects($this->any())->method('setLowStockDate')->willReturnSelf();
        $this->stockStateProviderMock->expects($this->once())
            ->method('verifyNotification')
            ->with($this->stockItemMock)
            ->willReturn(true);
        $this->dateTime->expects($this->once())
            ->method('gmtDate');
        $this->stockItemMock->expects($this->atLeastOnce())->method('setStockStatusChangedAuto')->willReturnSelf();
        $this->stockItemMock->expects($this->once())
            ->method('hasStockStatusChangedAutomaticallyFlag')
            ->willReturn(true);
        $this->stockItemMock->expects($this->once())
            ->method('getStockStatusChangedAutomaticallyFlag')
            ->willReturn(true);
        $this->stockItemMock->expects($this->once())->method('getWebsiteId')->willReturn(1);
        $this->stockItemMock->expects($this->once())->method('setWebsiteId')->with(1)->willReturnSelf();
        $this->stockItemMock->expects($this->once())->method('getStockId')->willReturn(1);
        $this->stockItemMock->expects($this->once())->method('setStockId')->with(1)->willReturnSelf();
        $this->stockItemResourceMock->expects($this->once())
            ->method('save')
            ->with($this->stockItemMock)
            ->willReturnSelf();
        $this->indexProcessorMock->expects($this->once())->method('reindexRow')->with($productId);

        $this->assertEquals($this->stockItemMock, $this->model->save($this->stockItemMock));
    }

    public function testSaveWithoutProductId()
    {
        $productId = 1;

        $this->stockItemMock->expects($this->any())->method('getProductId')->willReturn($productId);
        $this->productMock->expects($this->once())->method('load')->with($productId)->willReturnSelf();
        $this->productMock->expects($this->once())->method('getId')->willReturn(null);

        $this->assertEquals($this->stockItemMock, $this->model->save($this->stockItemMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveException()
    {
        $productId = 1;

        $this->stockItemMock->expects($this->any())->method('getProductId')->willReturn($productId);
        $this->productMock->expects($this->once())->method('load')->with($productId)->willReturnSelf();
        $this->productMock->expects($this->once())->method('getId')->willReturn($productId);
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('typeId');
        $this->stockConfigurationMock->expects($this->once())->method('isQty')->with('typeId')->willReturn(false);
        $this->stockItemMock->expects($this->once())->method('setQty')->with(0)->willReturnSelf();
        $this->stockItemMock->expects($this->once())->method('getWebsiteId')->willReturn(1);
        $this->stockItemMock->expects($this->once())->method('setWebsiteId')->with(1)->willReturnSelf();
        $this->stockItemMock->expects($this->once())->method('getStockId')->willReturn(1);
        $this->stockItemMock->expects($this->once())->method('setStockId')->with(1)->willReturnSelf();
        $this->stockItemResourceMock->expects($this->once())
            ->method('save')
            ->with($this->stockItemMock)
            ->willThrowException(new \Exception());

        $this->model->save($this->stockItemMock);
    }

    public function testGetList()
    {
        $criteriaMock = $this->getMockBuilder('Magento\CatalogInventory\Api\StockItemCriteriaInterface')
            ->getMock();
        $queryBuilderMock = $this->getMockBuilder('Magento\Framework\DB\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['setCriteria', 'setResource', 'create'])
            ->getMock();
        $queryMock = $this->getMockBuilder('Magento\Framework\DB\QueryInterface')
            ->getMock();
        $queryCollectionMock = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockItemCollectionInterface')
            ->getMock();

        $this->queryBuilderFactoryMock->expects($this->once())->method('create')->willReturn($queryBuilderMock);
        $queryBuilderMock->expects($this->once())->method('setCriteria')->with($criteriaMock)->willReturnSelf();
        $queryBuilderMock->expects($this->once())
            ->method('setResource')
            ->with($this->stockItemResourceMock)
            ->willReturnSelf();
        $queryBuilderMock->expects($this->once())->method('create')->willReturn($queryMock);
        $this->stockItemCollectionMock->expects($this->once())->method('create')->willReturn($queryCollectionMock);

        $this->assertEquals($queryCollectionMock, $this->model->getList($criteriaMock));
    }
}
