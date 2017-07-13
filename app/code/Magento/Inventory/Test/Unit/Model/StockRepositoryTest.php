<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Inventory\Model\ResourceModel\Stock as StockResource;
use Magento\Inventory\Model\ResourceModel\Stock\Collection as StockCollection;
use Magento\Inventory\Model\ResourceModel\Stock\CollectionFactory as StockCollectionFactory;
use Magento\Inventory\Model\Stock;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\Data\StockInterfaceFactory;
use Magento\InventoryApi\Api\Data\StockSearchResultsInterface;
use Magento\InventoryApi\Api\Data\StockSearchResultsInterfaceFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StockResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceStock;

    /**
     * @var StockInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockFactory;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    /**
     * @var StockCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockCollectionFactory;

    /**
     * @var StockSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockSearchResultsFactory;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

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
        $this->resourceStock = $this->getMockBuilder(StockResource::class)->disableOriginalConstructor()->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockFactory = $this->getMockBuilder(StockInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['process'])
            ->getMock();
        $this->stockCollectionFactory = $this->getMockBuilder(StockCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->stockSearchResultsFactory = $this->getMockBuilder(StockSearchResultsInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
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
                'resourceStock' => $this->resourceStock,
                'stockFactory' => $this->stockFactory,
                'collectionProcessor' => $this->collectionProcessor,
                'stockCollectionFactory' => $this->stockCollectionFactory,
                'stockSearchResultsFactory' => $this->stockSearchResultsFactory,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
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
        $this->resourceStock
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

        $this->resourceStock
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
        $this->resourceStock
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
        $this->resourceStock->expects($this->once())
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
        $items = [
            $this->getMockBuilder(Stock::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(Stock::class)->disableOriginalConstructor()->getMock()
        ];
        $totalCount = 2;
        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stockCollection = $this->getMockBuilder(StockCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stockCollection
            ->expects($this->once())
            ->method('getItems')
            ->willReturn($items);
        $stockCollection
            ->expects($this->once())
            ->method('getSize')
            ->willReturn($totalCount);
        $this->stockCollectionFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($stockCollection);

        $searchResults = $this->getMockBuilder(StockSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResults
            ->expects($this->once())
            ->method('setItems')
            ->with($items);
        $searchResults
            ->expects($this->once())
            ->method('setTotalCount')
            ->with($totalCount);
        $searchResults
            ->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteria);
        $this->stockSearchResultsFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchResults);

        $this->collectionProcessor
            ->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $stockCollection);

        self::assertSame($searchResults, $this->model->getList($searchCriteria));
    }

    public function testGetListWithoutSearchCriteria()
    {
        $items = [
            $this->getMockBuilder(Stock::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(Stock::class)->disableOriginalConstructor()->getMock()
        ];
        $totalCount = 2;

        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);

        $stockCollection = $this->getMockBuilder(StockCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stockCollection
            ->expects($this->once())
            ->method('getItems')
            ->willReturn($items);
        $stockCollection
            ->expects($this->once())
            ->method('getSize')
            ->willReturn($totalCount);
        $this->stockCollectionFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($stockCollection);

        $searchResults = $this->getMockBuilder(StockSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResults
            ->expects($this->once())
            ->method('setItems')
            ->with($items);
        $searchResults
            ->expects($this->once())
            ->method('setTotalCount')
            ->with($totalCount);
        $searchResults
            ->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteria);
        $this->stockSearchResultsFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchResults);

        $this->collectionProcessor
            ->expects($this->never())
            ->method('process');

        self::assertSame($searchResults, $this->model->getList());
    }

    public function testDelete()
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
        $this->resourceStock
            ->expects($this->once())
            ->method('load')
            ->with($this->stock, $stockId, StockInterface::STOCK_ID);

        $this->resourceStock
            ->expects($this->once())
            ->method('delete')
            ->with($this->stock);

        $this->model->delete($stockId);
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
        $this->resourceStock->expects($this->once())
            ->method('load')
            ->with(
                $this->stock,
                $stockId,
                StockInterface::STOCK_ID
            );

        $this->model->delete($stockId);
    }
}
