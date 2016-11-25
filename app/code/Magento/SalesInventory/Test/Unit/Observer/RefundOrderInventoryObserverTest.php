<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Test\Unit\Observer;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\SalesInventory\Model\Order\ReturnProcessor;
use Magento\SalesInventory\Observer\RefundOrderInventoryObserver;

class RefundOrderInventoryObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RefundOrderInventoryObserver
     */
    protected $observer;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceIndexer;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockIndexerProcessor;

    /**
     * @var \Magento\CatalogInventory\Api\StockManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockManagement;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockConfiguration;

    /**
     * @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventObserver;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var OrderRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepositoryMock;

    /**
     * @var ReturnProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $returnProcessorMock;

    /**
     * @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    protected function setUp()
    {
        $this->stockIndexerProcessor = $this->getMock(
            'Magento\CatalogInventory\Model\Indexer\Stock\Processor',
            ['reindexList'],
            [],
            '',
            false
        );

        $this->stockManagement = $this->getMock(
            'Magento\CatalogInventory\Model\StockManagement',
            [],
            [],
            '',
            false
        );

        $this->stockConfiguration = $this->getMockForAbstractClass(
            'Magento\CatalogInventory\Api\StockConfigurationInterface',
            [
                'isAutoReturnEnabled',
                'isDisplayProductStockStatus'
            ],
            '',
            false
        );

        $this->priceIndexer = $this->getMockBuilder('Magento\Catalog\Model\Indexer\Product\Price\Processor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->event = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getProduct', 'getCollection', 'getCreditmemo', 'getQuote', 'getWebsite'])
            ->getMock();

        $this->eventObserver = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->will($this->returnValue($this->event));

<<<<<<< HEAD
        $this->observer = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\CatalogInventory\Observer\RefundOrderInventoryObserver',
=======
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->returnProcessorMock = $this->getMockBuilder(ReturnProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->observer = $this->objectManagerHelper->getObject(
            \Magento\SalesInventory\Observer\RefundOrderInventoryObserver::class,
>>>>>>> e0d9191... MAGETWO-59074: Creditmemo return to stock only one unit of configurable product
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
        $isAutoReturnEnabled = true;

<<<<<<< HEAD
        $store = $this->getMock(
            'Magento\Store\Model\Store',
            ['getWebsiteId'],
            [],
            '',
            false
        );
        $store->expects($this->once())->method('getWebsiteId')->will($this->returnValue($websiteId));
=======
        $creditMemo = $this->getMock(\Magento\Sales\Model\Order\Creditmemo::class, [], [], '', false);
>>>>>>> e0d9191... MAGETWO-59074: Creditmemo return to stock only one unit of configurable product

        foreach ($ids as $id) {
            $item = $this->getCreditMemoItem($id);
            $items[] = $item;
        }
<<<<<<< HEAD
        $creditMemo = $this->getMock('Magento\Sales\Model\Order\Creditmemo', [], [], '', false);
=======

>>>>>>> e0d9191... MAGETWO-59074: Creditmemo return to stock only one unit of configurable product
        $creditMemo->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue($items));

        $this->stockConfiguration->expects($this->any())
            ->method('isAutoReturnEnabled')
            ->will($this->returnValue($isAutoReturnEnabled));

        $this->event->expects($this->once())
            ->method('getCreditmemo')
            ->will($this->returnValue($creditMemo));

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);

        $this->returnProcessorMock->expects($this->once())
            ->method('execute')
            ->with($creditMemo, $this->orderMock, $ids, $isAutoReturnEnabled);

        $this->observer->execute($this->eventObserver);
    }

    private function getCreditMemoItem($productId)
    {
        $backToStock = true;
        $item = $this->getMock(
<<<<<<< HEAD
            'Magento\Sales\Model\Order\Creditmemo\Item',
            ['getProductId', 'getOrderItem', 'getBackToStock', 'getQty', '__wakeup'],
=======
            \Magento\Sales\Model\Order\Creditmemo\Item::class,
            ['getOrderItemId', 'getBackToStock', 'getQty', '__wakeup'],
>>>>>>> e0d9191... MAGETWO-59074: Creditmemo return to stock only one unit of configurable product
            [],
            '',
            false
        );
<<<<<<< HEAD
        $orderItem = $this->getMock('Magento\Sales\Model\Order\Item', ['getParentItemId', '__wakeup'], [], '', false);
        $orderItem->expects($this->any())->method('getParentItemId')->willReturn($parentItemId);
        $item->expects($this->any())->method('getOrderItem')->willReturn($orderItem);
        $item->expects($this->any())->method('getProductId')->will($this->returnValue($productId));
=======
>>>>>>> e0d9191... MAGETWO-59074: Creditmemo return to stock only one unit of configurable product
        $item->expects($this->any())->method('getBackToStock')->willReturn($backToStock);
        $item->expects($this->any())->method('getOrderItemId')->willReturn($productId);
        return $item;
    }
}
