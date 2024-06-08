<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Stock;

use Magento\CatalogInventory\Api\Data\StockCollectionInterface;
use Magento\CatalogInventory\Api\Data\StockCollectionInterfaceFactory;
use Magento\CatalogInventory\Api\StockCriteriaInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\CatalogInventory\Model\Stock\StockRepository;
use Magento\CatalogInventory\Model\StockFactory;
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
class StockRepositoryTest extends TestCase
{
    /**
     * @var StockRepository
     */
    protected $model;

    /**
     * @var \Magento\CatalogInventory\Model\Stock|MockObject
     */
    protected $stockMock;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock|MockObject
     */
    protected $stockResourceMock;

    /**
     * @var StockFactory|MockObject
     */
    protected $stockFactoryMock;

    /**
     * @var StockCollectionInterfaceFactory|MockObject
     */
    protected $stockCollectionMock;

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
        $this->stockMock = $this->getMockBuilder(Stock::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockResourceMock = $this->getMockBuilder(\Magento\CatalogInventory\Model\ResourceModel\Stock::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockFactoryMock = $this->getMockBuilder(
            StockFactory::class
        )
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockCollectionMock = $this->getMockBuilder(
            StockCollectionInterfaceFactory::class
        )
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryBuilderFactoryMock = $this->getMockBuilder(QueryBuilderFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->mapperMock = $this->getMockBuilder(MapperFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockRegistryStorage = $this->getMockBuilder(StockRegistryStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            StockRepository::class,
            [
                'resource' => $this->stockResourceMock,
                'stockFactory' => $this->stockFactoryMock,
                'collectionFactory' => $this->stockCollectionMock,
                'queryBuilderFactory' => $this->queryBuilderFactoryMock,
                'mapperFactory' => $this->mapperMock,
                'stockRegistryStorage' => $this->stockRegistryStorage,
            ]
        );
    }

    public function testSave()
    {
        $this->stockResourceMock->expects($this->once())
            ->method('save')
            ->with($this->stockMock)
            ->willReturnSelf();

        $this->assertEquals($this->stockMock, $this->model->save($this->stockMock));
    }

    public function testSaveException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->stockResourceMock->expects($this->once())
            ->method('save')
            ->with($this->stockMock)
            ->willThrowException(new \Exception());

        $this->model->save($this->stockMock);
    }

    public function testGetList()
    {
        $criteriaMock = $this->getMockBuilder(StockCriteriaInterface::class)
            ->getMock();
        $queryBuilderMock = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setCriteria', 'setResource', 'create'])
            ->getMock();
        $queryMock = $this->getMockBuilder(QueryInterface::class)
            ->getMock();
        $queryCollectionMock = $this->getMockBuilder(StockCollectionInterface::class)
            ->getMock();

        $this->queryBuilderFactoryMock->expects($this->once())->method('create')->willReturn($queryBuilderMock);
        $queryBuilderMock->expects($this->once())->method('setCriteria')->with($criteriaMock)->willReturnSelf();
        $queryBuilderMock->expects($this->once())
            ->method('setResource')
            ->with($this->stockResourceMock)
            ->willReturnSelf();
        $queryBuilderMock->expects($this->once())->method('create')->willReturn($queryMock);
        $this->stockCollectionMock->expects($this->once())->method('create')->willReturn($queryCollectionMock);

        $this->assertEquals($queryCollectionMock, $this->model->getList($criteriaMock));
    }

    public function testDelete()
    {
        $this->stockRegistryStorage->expects($this->once())->method('removeStock');

        $this->stockResourceMock->expects($this->once())
            ->method('delete')
            ->with($this->stockMock)
            ->willReturnSelf();

        $this->assertTrue($this->model->delete($this->stockMock));
    }

    public function testDeleteException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotDeleteException');
        $this->stockResourceMock->expects($this->once())
            ->method('delete')
            ->with($this->stockMock)
            ->willThrowException(new \Exception());

        $this->model->delete($this->stockMock);
    }

    public function testDeleteById()
    {
        $id = 1;

        $this->stockFactoryMock->expects($this->once())->method('create')->willReturn($this->stockMock);
        $this->stockResourceMock->expects($this->once())->method('load')->with($this->stockMock, $id);
        $this->stockMock->expects($this->once())->method('getId')->willReturn($id);

        $this->assertTrue($this->model->deleteById($id));
    }

    public function testDeleteByIdException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotDeleteException');
        $this->expectExceptionMessage('Unable to remove Stock with id "1"');
        $id = 1;

        $this->stockFactoryMock->expects($this->once())->method('create')->willReturn($this->stockMock);
        $this->stockResourceMock->expects($this->once())->method('load')->with($this->stockMock, $id);
        $this->stockMock->expects($this->once())->method('getId')->willReturn(null);

        $this->assertTrue($this->model->deleteById($id));
    }
}
