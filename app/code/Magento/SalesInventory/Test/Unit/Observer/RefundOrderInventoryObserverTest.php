<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesInventory\Test\Unit\Observer;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\CatalogInventory\Model\StockManagement;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Item;
use Magento\Sales\Model\OrderRepository;
use Magento\SalesInventory\Model\Order\ReturnProcessor;
use Magento\SalesInventory\Observer\RefundOrderInventoryObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RefundOrderInventoryObserverTest extends TestCase
{
    /**
     * @var RefundOrderInventoryObserver
     */
    protected $observer;

    /**
     * @var Processor|MockObject
     */
    protected $priceIndexer;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor|MockObject
     */
    protected $stockIndexerProcessor;

    /**
     * @var StockManagementInterface|MockObject
     */
    protected $stockManagement;

    /**
     * @var StockConfigurationInterface|MockObject
     */
    protected $stockConfiguration;

    /**
     * @var Event|MockObject
     */
    protected $event;

    /**
     * @var Observer|MockObject
     */
    protected $eventObserver;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var OrderRepository|MockObject
     */
    protected $orderRepositoryMock;

    /**
     * @var ReturnProcessor|MockObject
     */
    protected $returnProcessorMock;

    /**
     * @var OrderInterface|MockObject
     */
    private $orderMock;

    protected function setUp(): void
    {
        $this->stockIndexerProcessor = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Indexer\Stock\Processor::class,
            ['reindexList']
        );

        $this->stockManagement = $this->createMock(StockManagement::class);

        $this->stockConfiguration = $this->getMockForAbstractClass(
            StockConfigurationInterface::class,
            [
                'isAutoReturnEnabled',
                'isDisplayProductStockStatus'
            ],
            '',
            false
        );

        $this->priceIndexer = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct', 'getCollection', 'getCreditmemo', 'getQuote', 'getWebsite'])
            ->getMock();

        $this->eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->event);

        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->returnProcessorMock = $this->getMockBuilder(ReturnProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManager($this);

        $this->observer = $this->objectManagerHelper->getObject(
            RefundOrderInventoryObserver::class,
            [
                'stockConfiguration' => $this->stockConfiguration,
                'stockManagement' => $this->stockManagement,
                'stockIndexerProcessor' => $this->stockIndexerProcessor,
                'priceIndexer' => $this->priceIndexer,
            ]
        );

        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->observer,
            'orderRepository',
            $this->orderRepositoryMock
        );
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->observer,
            'returnProcessor',
            $this->returnProcessorMock
        );
    }

    public function testRefundOrderInventory()
    {
        $ids = ['1', '14'];
        $items = [];

        $creditMemo = $this->createMock(Creditmemo::class);

        foreach ($ids as $id) {
            $item = $this->getCreditMemoItem($id);
            $items[] = $item;
        }

        $creditMemo->expects($this->once())
            ->method('getItems')
            ->willReturn($items);
        $this->event->expects($this->once())
            ->method('getCreditmemo')
            ->willReturn($creditMemo);

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);

        $this->returnProcessorMock->expects($this->once())
            ->method('execute')
            ->with($creditMemo, $this->orderMock, $ids);

        $this->observer->execute($this->eventObserver);
    }

    /**
     * @param $productId
     * @return MockObject
     */
    private function getCreditMemoItem($productId)
    {
        $backToStock = true;
        $item = $this->getMockBuilder(Item::class)
            ->addMethods(['getBackToStock'])
            ->onlyMethods(['getOrderItemId', 'getQty', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $item->expects($this->any())->method('getBackToStock')->willReturn($backToStock);
        $item->expects($this->any())->method('getOrderItemId')->willReturn($productId);
        return $item;
    }
}
