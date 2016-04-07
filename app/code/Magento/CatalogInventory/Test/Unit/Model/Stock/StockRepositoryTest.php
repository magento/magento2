<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\Stock;

use Magento\CatalogInventory\Model\Stock\StockRepository;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class StockRepositoryTest
 */
class StockRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StockRepository
     */
    protected $model;

    /**
     * @var \Magento\CatalogInventory\Model\Stock |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockMock;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockResourceMock;

    /**
     * @var Magento\CatalogInventory\Model\StockFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockFactoryMock;

    /**
     * @var Magento\CatalogInventory\Api\Data\StockCollectionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockCollectionMock;

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
        $this->stockMock = $this->getMockBuilder('\Magento\CatalogInventory\Model\Stock')
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockResourceMock = $this->getMockBuilder('\Magento\CatalogInventory\Model\ResourceModel\Stock')
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockFactoryMock = $this->getMockBuilder(
            'Magento\CatalogInventory\Model\StockFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockCollectionMock = $this->getMockBuilder(
            'Magento\CatalogInventory\Api\Data\StockCollectionInterfaceFactory'
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

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveException()
    {
        $this->stockResourceMock->expects($this->once())
            ->method('save')
            ->with($this->stockMock)
            ->willThrowException(new \Exception());

        $this->model->save($this->stockMock);
    }

    public function testGetList()
    {
        $criteriaMock = $this->getMockBuilder('Magento\CatalogInventory\Api\StockCriteriaInterface')
            ->getMock();
        $queryBuilderMock = $this->getMockBuilder('Magento\Framework\DB\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['setCriteria', 'setResource', 'create'])
            ->getMock();
        $queryMock = $this->getMockBuilder('Magento\Framework\DB\QueryInterface')
            ->getMock();
        $queryCollectionMock = $this->getMockBuilder('Magento\CatalogInventory\Api\Data\StockCollectionInterface')
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

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function testDeleteException()
    {
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

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage Unable to remove Stock with id "1"
     */
    public function testDeleteByIdException()
    {
        $id = 1;

        $this->stockFactoryMock->expects($this->once())->method('create')->willReturn($this->stockMock);
        $this->stockResourceMock->expects($this->once())->method('load')->with($this->stockMock, $id);
        $this->stockMock->expects($this->once())->method('getId')->willReturn(null);

        $this->assertTrue($this->model->deleteById($id));
    }
}
