<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Observer
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
     * @var \Magento\CatalogInventory\Model\Resource\Stock|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceStock;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockManagement;

    /**
     * @var \Magento\CatalogInventory\Api\StockIndexInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockIndex;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockHelper;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockConfiguration;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemRepository;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterfaceBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemBuilder;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItem;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockStatusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStatus;

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
        $this->stockStatus = $this->getMockBuilder('Magento\CatalogInventory\Model\Stock\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockFactory = $this->getMockBuilder('Magento\CatalogInventory\Model\StockFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->catalogInventoryData = $this->getMock('Magento\CatalogInventory\Helper\Data', [], [], '', false);
        $this->stock = $this->getMock('Magento\CatalogInventory\Model\Stock', [], [], '', false);
        $this->stockIndexProcessor = $this->getMock(
            '\Magento\Catalog\Model\Indexer\Product\Stock\Processor',
            ['reindexList', 'reindexRow'],
            [],
            '',
            false
        );
        $this->stockIndexerProcessor = $this->getMock(
            '\Magento\CatalogInventory\Model\Indexer\Stock\Processor',
            ['reindexList'],
            [],
            '',
            false
        );
        $this->resourceStock = $this->getMock(
            '\Magento\CatalogInventory\Model\Resource\Stock',
            ['updateSetOutOfStock', 'updateSetInStock', 'updateLowStockDate', '__wakeup'],
            [],
            '',
            false
        );
        $this->stockRegistry = $this->getMockForAbstractClass(
            '\Magento\CatalogInventory\Api\StockRegistryInterface',
            ['getStockItem'],
            '',
            false
        );
        $this->stockRegistry->expects($this->any())->method('getStockItem')->willReturn($this->stockItem);
        $this->stockManagement = $this->getMock(
            'Magento\CatalogInventory\Model\StockManagement',
            [],
            [],
            '',
            false
        );
        $this->stockIndex = $this->getMockForAbstractClass(
            '\Magento\CatalogInventory\Api\StockIndexInterface',
            ['rebuild'],
            '',
            false
        );

        $this->stockHelper = $this->getMock(
            '\Magento\CatalogInventory\Helper\Stock',
            [
                'assignStatusToProduct',
                'addStockStatusToProducts',
                'addStockStatusToSelect'
            ],
            [],
            '',
            false
        );
        $this->stockConfiguration = $this->getMockForAbstractClass(
            '\Magento\CatalogInventory\Api\StockConfigurationInterface',
            [
                'isAutoReturnEnabled',
                'isDisplayProductStockStatus'
            ],
            '',
            false
        );
        $this->stockItemRepository = $this->getMockForAbstractClass(
            '\Magento\CatalogInventory\Api\StockItemRepositoryInterface',
            ['save'],
            '',
            false
        );
        $this->stockItemBuilder = $this->getMock(
            '\Magento\CatalogInventory\Api\Data\StockItemInterfaceBuilder',
            ['mergeDataObjectWithArray'],
            [],
            '',
            false
        );
        $this->priceIndexer = $this->getMockBuilder('Magento\Catalog\Model\Indexer\Product\Price\Processor')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->observer = $objectManagerHelper->getObject(
            'Magento\CatalogInventory\Model\Observer',
            [
                'priceIndexer' => $this->priceIndexer,
                'stockIndexerProcessor' => $this->stockIndexerProcessor,
                'resourceStock' => $this->resourceStock,
                'stockRegistry' => $this->stockRegistry,
                'stockManagement' => $this->stockManagement,
                'stockIndex' => $this->stockIndex,
                'stockHelper' => $this->stockHelper,
                'stockConfiguration' => $this->stockConfiguration,
                'stockItemRepository' => $this->stockItemRepository,
                'stockItemBuilder' => $this->stockItemBuilder
            ]
        );

        $this->event = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getProduct', 'getCollection', 'getCreditmemo', 'getQuote', 'getWebsite'])
            ->getMock();
        $this->eventObserver = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();
    }

    public function testAddInventoryData()
    {
        $stockStatus = true;
        $product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getStockStatus'])
            ->getMock();
        $product->expects($this->once())
            ->method('getStockStatus')
            ->will($this->returnValue($stockStatus));

        $this->event->expects($this->once())
            ->method('getProduct')
            ->will($this->returnValue($product));
        $this->stockHelper->expects($this->once())
            ->method('assignStatusToProduct')
            ->with($product, $stockStatus)
            ->will($this->returnSelf());

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->will($this->returnValue($this->event));
        $this->assertEquals($this->observer, $this->observer->addInventoryData($this->eventObserver));
    }

    public function testAddStockStatusToCollection()
    {
        $productCollection = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->event->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($productCollection));
        $this->stockHelper->expects($this->once())
            ->method('addStockStatusToProducts')
            ->with($productCollection)
            ->will($this->returnSelf());

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->will($this->returnValue($this->event));
        $this->assertEquals($this->observer, $this->observer->addStockStatusToCollection($this->eventObserver));
    }

    public function testAddInventoryDataToCollection()
    {
        $productCollection = $this->getMockBuilder('Magento\Catalog\Model\Resource\Product\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->event->expects($this->once())
            ->method('getCollection')
            ->will($this->returnValue($productCollection));
        $this->stockHelper->expects($this->once())
            ->method('addStockStatusToProducts')
            ->with($productCollection)
            ->will($this->returnSelf());

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->will($this->returnValue($this->event));
        $this->assertEquals($this->observer, $this->observer->addStockStatusToCollection($this->eventObserver));
    }

    public function testSaveInventoryData()
    {
        $productId = 4;
        $websiteId = 5;
        $stockData = null;
        $websitesChanged = true;
        $statusChanged = true;

        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getStockData', 'getIsChangedWebsites', 'dataHasChangedFor', 'getId', 'getStore', '__wakeup'],
            [],
            '',
            false
        );
        $product->expects($this->once())->method('getStockData')->will($this->returnValue($stockData));
        if ($stockData === null) {
            $product->expects($this->any())->method('getIsChangedWebsites')->will($this->returnValue($websitesChanged));
            $product->expects($this->any())->method('dataHasChangedFor')->will($this->returnValue($statusChanged));
            if ($websitesChanged || $statusChanged) {
                $product->expects($this->once())->method('getId')->will($this->returnValue($productId));
                $store = $this->getMock(
                    'Magento\Store\Model\Store',
                    ['getWebsiteId', '__wakeup'],
                    [],
                    '',
                    false
                );
                $store->expects($this->once())->method('getWebsiteId')->will($this->returnValue($websiteId));
                $product->expects($this->once())->method('getStore')->will($this->returnValue($store));
                $this->stockIndex->expects($this->once())->method('rebuild')->will(
                    $this->returnValue(true)
                );
            }
        } else {
            $stockItem = $this->getMockForAbstractClass(
                'Magento\CatalogInventory\Api\Data\StockItem',
                ['__wakeup'],
                '',
                false
            );
            $this->stockRegistry->expects($this->once())
                ->method('getStockItem')
                ->with($productId, $websiteId)
                ->will($this->returnValue($stockItem));
        }

        $this->event->expects($this->once())
            ->method('getProduct')
            ->will($this->returnValue($product));

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->will($this->returnValue($this->event));
        $this->assertEquals($this->observer, $this->observer->saveInventoryData($this->eventObserver));
    }

    public function testCheckoutAllSubmitAfter()
    {
        $inventoryProcessed = false;
        $websiteId = 0;
        $itemsToRegister = [];
        $itemsToReindex = [];

        $quote = $this->getMock(
            '\Magento\Sales\Model\Quote',
            ['getInventoryProcessed', 'setInventoryProcessed', 'getAllItems', 'getStore', '__wakeup'],
            [],
            '',
            false
        );
        $quote->expects($this->atLeastOnce())
            ->method('getInventoryProcessed', 'setInventoryProcessed', 'getStore')
            ->will($this->returnValue($inventoryProcessed));
        $store = $this->getMock(
            'Magento\Store\Model\Store',
            ['getWebsiteId', '__wakeup'],
            [],
            '',
            false
        );
        $store->expects($this->once())->method('getWebsiteId')->will($this->returnValue($websiteId));
        $quote->expects($this->once())->method('getStore')->will($this->returnValue($store));
        $quote->expects($this->any())->method('getAllItems')->will($this->returnValue($itemsToRegister));

        $this->stockManagement->expects($this->once())->method('registerProductsSale')->will(
            $this->returnValue($itemsToReindex)
        );

        $this->event->expects($this->atLeastOnce())
            ->method('getQuote')
            ->will($this->returnValue($quote));

        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->will($this->returnValue($this->event));
        $this->assertEquals($this->observer, $this->observer->checkoutAllSubmitAfter($this->eventObserver));
    }

    public function testRefundOrderInventory()
    {
        $websiteId = 0;
        $ids = ['1', '14'];
        $items = [];
        $isAutoReturnEnabled = true;

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
        $store = $this->getMock(
            'Magento\Store\Model\Store',
            ['getWebsiteId', '__wakeup'],
            [],
            '',
            false
        );
        $store->expects($this->once())->method('getWebsiteId')->will($this->returnValue($websiteId));
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
        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->will($this->returnValue($this->event));
        $this->observer->refundOrderInventory($this->eventObserver);
    }

    public function testUpdateItemsStockUponConfigChange()
    {
        $websiteId = 1;
        $this->resourceStock->expects($this->once())->method('updateSetOutOfStock')->willReturn(null);
        $this->resourceStock->expects($this->once())->method('updateSetInStock')->willReturn(null);
        $this->resourceStock->expects($this->once())->method('updateLowStockDate')->willReturn(null);

        $this->event->expects($this->once())
            ->method('getWebsite')
            ->will($this->returnValue($websiteId));
        $this->eventObserver->expects($this->atLeastOnce())
            ->method('getEvent')
            ->will($this->returnValue($this->event));
        $this->observer->updateItemsStockUponConfigChange($this->eventObserver);
    }

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
