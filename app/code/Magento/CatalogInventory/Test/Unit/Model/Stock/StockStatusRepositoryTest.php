<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Stock;

use Magento\CatalogInventory\Api\Data as InventoryApiData;
use Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface;
use Magento\CatalogInventory\Api\Data\StockStatusCollectionInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterface;
use Magento\CatalogInventory\Model\Stock\Status;
use Magento\CatalogInventory\Model\Stock\StatusFactory;
use Magento\CatalogInventory\Model\Stock\StockStatusRepository;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\Framework\DB\MapperFactory;
use Magento\Framework\DB\QueryBuilder;
use Magento\Framework\DB\QueryBuilderFactory;
use Magento\Framework\DB\QueryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockStatusRepositoryTest extends TestCase
{
    /**
     * @var StockStatusRepository
     */
    protected $model;

    /**
     * @var Status|MockObject
     */
    protected $stockStatusMock;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Status|MockObject
     */
    protected $stockStatusResourceMock;

    /**
     * @var StatusFactory|MockObject
     */
    protected $stockStatusFactoryMock;

    /**
     * @var InventoryApiData\StockStatusCollectionInterfaceFactory|MockObject
     */
    protected $stockStatusCollectionMock;

    /**
     * @var QueryBuilderFactory|MockObject
     */
    protected $queryBuilderFactoryMock;

    /**
     * @var MapperFactory|MockObject
     */
    protected $mapperMock;

    /**
     * @var StockRegistryStorage|MockObject
     */
    protected $stockRegistryStorage;

    protected function setUp(): void
    {
        $this->stockStatusMock = $this->getMockBuilder(Status::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockStatusResourceMock =
            $this->getMockBuilder(\Magento\CatalogInventory\Model\ResourceModel\Stock\Status::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->stockStatusFactoryMock = $this->getMockBuilder(
            StatusFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockStatusCollectionMock = $this->getMockBuilder(
            StockStatusCollectionInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryBuilderFactoryMock = $this->getMockBuilder(QueryBuilderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->mapperMock = $this->getMockBuilder(MapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockRegistryStorage = $this->getMockBuilder(StockRegistryStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            StockStatusRepository::class,
            [
                'resource' => $this->stockStatusResourceMock,
                'stockStatusFactory' => $this->stockStatusFactoryMock,
                'collectionFactory' => $this->stockStatusCollectionMock,
                'queryBuilderFactory' => $this->queryBuilderFactoryMock,
                'mapperFactory' => $this->mapperMock,
                'stockRegistryStorage' => $this->stockRegistryStorage,
            ]
        );
    }

    public function testSave()
    {
        $this->stockStatusResourceMock->expects($this->once())
            ->method('save')
            ->with($this->stockStatusMock)
            ->willReturnSelf();

        $this->assertEquals($this->stockStatusMock, $this->model->save($this->stockStatusMock));
    }

    public function testSaveException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->stockStatusResourceMock->expects($this->once())
            ->method('save')
            ->with($this->stockStatusMock)
            ->willThrowException(new \Exception());

        $this->model->save($this->stockStatusMock);
    }

    public function testGetList()
    {
        $criteriaMock = $this->getMockBuilder(StockStatusCriteriaInterface::class)
            ->getMock();
        $queryBuilderMock = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCriteria', 'setResource', 'create'])
            ->getMock();
        $queryMock = $this->getMockBuilder(QueryInterface::class)
            ->getMock();
        $queryCollectionMock = $this->getMockBuilder(
            StockStatusCollectionInterface::class
        )->getMock();

        $this->queryBuilderFactoryMock->expects($this->once())->method('create')->willReturn($queryBuilderMock);
        $queryBuilderMock->expects($this->once())->method('setCriteria')->with($criteriaMock)->willReturnSelf();
        $queryBuilderMock->expects($this->once())
            ->method('setResource')
            ->with($this->stockStatusResourceMock)
            ->willReturnSelf();
        $queryBuilderMock->expects($this->once())->method('create')->willReturn($queryMock);
        $this->stockStatusCollectionMock->expects($this->once())->method('create')->willReturn($queryCollectionMock);

        $this->assertEquals($queryCollectionMock, $this->model->getList($criteriaMock));
    }

    public function testDelete()
    {
        $productId = 1;
        $this->stockStatusMock->expects($this->atLeastOnce())->method('getProductId')->willReturn($productId);
        $this->stockRegistryStorage->expects($this->once())->method('removeStockStatus')->with($productId);

        $this->stockStatusResourceMock->expects($this->once())
            ->method('delete')
            ->with($this->stockStatusMock)
            ->willReturnSelf();

        $this->assertTrue($this->model->delete($this->stockStatusMock));
    }

    public function testDeleteException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotDeleteException');
        $this->stockStatusResourceMock->expects($this->once())
            ->method('delete')
            ->with($this->stockStatusMock)
            ->willThrowException(new \Exception());

        $this->model->delete($this->stockStatusMock);
    }

    public function testDeleteById()
    {
        $id = 1;

        $this->stockStatusFactoryMock->expects($this->once())->method('create')->willReturn($this->stockStatusMock);
        $this->stockStatusResourceMock->expects($this->once())->method('load')->with($this->stockStatusMock, $id);

        $this->assertTrue($this->model->deleteById($id));
    }

    public function testDeleteByIdException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotDeleteException');
        $id = 1;

        $this->stockStatusFactoryMock->expects($this->once())->method('create')->willReturn($this->stockStatusMock);
        $this->stockStatusResourceMock->expects($this->once())->method('load')->with($this->stockStatusMock, $id);
        $this->stockStatusResourceMock->expects($this->once())
            ->method('delete')
            ->with($this->stockStatusMock)
            ->willThrowException(new \Exception());

        $this->assertTrue($this->model->deleteById($id));
    }
}
