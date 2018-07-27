<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\Stock;

use Magento\CatalogInventory\Model\Stock\StockStatusRepository;
use Magento\CatalogInventory\Api\Data as InventoryApiData;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class StockStatusRepositoryTest
 */
class StockStatusRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StockStatusRepository
     */
    protected $model;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\Status|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStatusMock;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Status|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStatusResourceMock;

    /**
     * @var Magento\CatalogInventory\Model\Stock\StatusFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStatusFactoryMock;

    /**
     * @var InventoryApiData\StockStatusCollectionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStatusCollectionMock;

    /**
     * @var \Magento\Framework\DB\QueryBuilderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $queryBuilderFactoryMock;

    /**
     * @var \Magento\Framework\DB\MapperFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapperMock;

    /**
     * @var StockRegistryStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistryStorage;

    protected function setUp()
    {
        $this->stockStatusMock = $this->getMockBuilder('\Magento\CatalogInventory\Model\Stock\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockStatusResourceMock =
            $this->getMockBuilder('\Magento\CatalogInventory\Model\ResourceModel\Stock\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockStatusFactoryMock = $this->getMockBuilder(
            'Magento\CatalogInventory\Model\Stock\StatusFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockStatusCollectionMock = $this->getMockBuilder(
            'Magento\CatalogInventory\Api\Data\StockStatusCollectionInterfaceFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryBuilderFactoryMock = $this->getMockBuilder('Magento\Framework\DB\QueryBuilderFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->mapperMock = $this->getMockBuilder('Magento\Framework\DB\MapperFactory')
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

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveException()
    {
        $this->stockStatusResourceMock->expects($this->once())
            ->method('save')
            ->with($this->stockStatusMock)
            ->willThrowException(new \Exception());

        $this->model->save($this->stockStatusMock);
    }

    public function testGetList()
    {
        $criteriaMock = $this->getMockBuilder('Magento\CatalogInventory\Api\StockStatusCriteriaInterface')
            ->getMock();
        $queryBuilderMock = $this->getMockBuilder('Magento\Framework\DB\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['setCriteria', 'setResource', 'create'])
            ->getMock();
        $queryMock = $this->getMockBuilder('Magento\Framework\DB\QueryInterface')
            ->getMock();
        $queryCollectionMock = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface')
            ->getMock();

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

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function testDeleteException()
    {
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

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function testDeleteByIdException()
    {
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
