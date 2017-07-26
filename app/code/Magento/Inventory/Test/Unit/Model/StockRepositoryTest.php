<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Inventory\Model\ResourceModel\Stock as StockResource;
use Magento\Inventory\Model\Stock;
use Magento\Inventory\Model\StockRepository\GetList;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\Data\StockInterfaceFactory;
use Magento\InventoryApi\Api\Data\StockSearchResultsInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StockResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockResource;

    /**
     * @var StockInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockFactory;

    /**
     * @var GetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $getList;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var Stock|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stock;

    /**
     * @var \Magento\Inventory\Model\StockRepository
     */
    private $model;

    protected function setUp()
    {
        $this->stockResource = $this->getMockBuilder(StockResource::class)->disableOriginalConstructor()->getMock();
        $this->stockFactory = $this->getMockBuilder(StockInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->getList = $this->getMockBuilder(GetList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stock = $this->getMockBuilder(Stock::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Inventory\Model\StockRepository::class,
            [
                'stockResource' => $this->stockResource,
                'stockFactory' => $this->stockFactory,
                'getList' => $this->getList,
                'logger' => $this->loggerMock,
            ]
        );
    }

    public function testSave()
    {
        $stockId = 42;

        $this->stock
            ->expects($this->once())
            ->method('getStockId')
            ->willReturn($stockId);
        $this->stockResource
            ->expects($this->once())
            ->method('save')
            ->with($this->stock);

        self::assertEquals($stockId, $this->model->save($this->stock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveErrorExpectsException()
    {
        $message = 'some message';

        $this->stockResource
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new \Exception($message));

        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with($message);

        $this->model->save($this->stock);
    }

    public function testGet()
    {
        $stockId = 345;

        $this->stock
            ->expects($this->once())
            ->method('getStockId')
            ->willReturn($stockId);
        $this->stockFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->stock);
        $this->stockResource
            ->expects($this->once())
            ->method('load')
            ->with($this->stock, $stockId, StockInterface::STOCK_ID);

        self::assertSame($this->stock, $this->model->get($stockId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetErrorExpectsException()
    {
        $stockId = 345;

        $this->stock
            ->expects($this->once())
            ->method('getStockId')
            ->willReturn(null);
        $this->stockFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->stock);
        $this->stockResource->expects($this->once())
            ->method('load')
            ->with(
                $this->stock,
                $stockId,
                StockInterface::STOCK_ID
            );

        $this->model->get($stockId);
    }

    public function testGetListWithSearchCriteria()
    {
        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResult = $this->getMockBuilder(StockSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->getList
            ->expects($this->once())
            ->method('execute')
            ->with($searchCriteria)
            ->willReturn($searchResult);

        self::assertSame($searchResult, $this->model->getList($searchCriteria));
    }

    public function testGetListWithoutSearchCriteria()
    {
        $searchResult = $this->getMockBuilder(StockSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->getList
            ->expects($this->once())
            ->method('execute')
            ->willReturn($searchResult);

        self::assertSame($searchResult, $this->model->getList());
    }

    public function testDeleteById()
    {
        $stockId = 345;
        $this->stock
            ->expects($this->once())
            ->method('getStockId')
            ->willReturn($stockId);
        $this->stockFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->stock);
        $this->stockResource
            ->expects($this->once())
            ->method('load')
            ->with($this->stock, $stockId, StockInterface::STOCK_ID);

        $this->stockResource
            ->expects($this->once())
            ->method('delete')
            ->with($this->stock);

        $this->model->deleteById($stockId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testDeleteErrorExpectsException()
    {
        $stockId = 0;

        $this->stock
            ->expects($this->once())
            ->method('getStockId')
            ->willReturn(null);
        $this->stockFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->stock);
        $this->stockResource->expects($this->once())
            ->method('load')
            ->with(
                $this->stock,
                $stockId,
                StockInterface::STOCK_ID
            );

        $this->model->deleteById($stockId);
    }
}
