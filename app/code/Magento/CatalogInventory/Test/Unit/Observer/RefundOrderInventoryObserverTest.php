<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Observer;

use Magento\CatalogInventory\Observer\RefundOrderInventoryObserver;

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

        $this->observer = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\CatalogInventory\Observer\RefundOrderInventoryObserver',
            [
                'stockConfiguration' => $this->stockConfiguration,
                'stockManagement' => $this->stockManagement,
                'stockIndexerProcessor' => $this->stockIndexerProcessor,
                'priceIndexer' => $this->priceIndexer,
            ]
        );
    }

    public function testRefundOrderInventory()
    {
        $websiteId = 0;
        $ids = ['1', '14'];
        $items = [];
        $isAutoReturnEnabled = true;

        $store = $this->getMock(
            'Magento\Store\Model\Store',
            ['getWebsiteId'],
            [],
            '',
            false
        );
        $store->expects($this->once())->method('getWebsiteId')->will($this->returnValue($websiteId));

        $itemsToUpdate = [];
        foreach ($ids as $id) {
            $item = $this->getCreditMemoItem($id);
            $items[] = $item;
            $itemsToUpdate[$item->getProductId()] = $item->getQty();
        }
        $creditMemo = $this->getMock('Magento\Sales\Model\Order\Creditmemo', [], [], '', false);
        $creditMemo->expects($this->once())
            ->method('getAllItems')
            ->will($this->returnValue($items));
        $creditMemo->expects($this->once())->method('getStore')->will($this->returnValue($store));

        $this->stockConfiguration->expects($this->any())
            ->method('isAutoReturnEnabled')
            ->will($this->returnValue($isAutoReturnEnabled));

        $this->stockManagement->expects($this->once())
            ->method('revertProductsSale')
            ->with($itemsToUpdate, $websiteId);

        $this->stockIndexerProcessor->expects($this->once())
            ->method('reindexList')
            ->with($ids);

        $this->priceIndexer->expects($this->once())
            ->method('reindexList')
            ->with($ids);

        $this->event->expects($this->once())
            ->method('getCreditmemo')
            ->will($this->returnValue($creditMemo));

        $this->observer->execute($this->eventObserver);
    }

    /**
     * @param $productId
     * @return mixed
     */
    private function getCreditMemoItem($productId)
    {
        $parentItemId = false;
        $backToStock = true;
        $qty = 1;
        $item = $this->getMock(
            'Magento\Sales\Model\Order\Creditmemo\Item',
            ['getProductId', 'getOrderItem', 'getBackToStock', 'getQty', '__wakeup'],
            [],
            '',
            false
        );
        $orderItem = $this->getMock('Magento\Sales\Model\Order\Item', ['getParentItemId', '__wakeup'], [], '', false);
        $orderItem->expects($this->any())->method('getParentItemId')->willReturn($parentItemId);
        $item->expects($this->any())->method('getOrderItem')->willReturn($orderItem);
        $item->expects($this->any())->method('getProductId')->will($this->returnValue($productId));
        $item->expects($this->any())->method('getBackToStock')->willReturn($backToStock);
        $item->expects($this->any())->method('getQty')->willReturn($qty);
        return $item;
    }
}
