<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Reorder;

use Magento\Customer\Model\Context;

/**
 * Class SidebarTest
 *
 * @package Magento\Sales\Block\Reorder
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SidebarTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Block\Reorder\Sidebar|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $block;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderConfig;

    /**
     * @var \Magento\Framework\App\Http\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpContext;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderCollection;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $stockItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    protected function setUp()
    {
        $this->markTestIncomplete('MAGETWO-36789');
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->httpContext = $this->createPartialMock(\Magento\Framework\App\Http\Context::class, ['getValue']);
        $this->orderCollectionFactory = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\CollectionFactory::class,
            ['create']
        );
        $this->customerSession = $this->createPartialMock(\Magento\Customer\Model\Session::class, ['getCustomerId']);
        $this->orderConfig = $this->createPartialMock(
            \Magento\Sales\Model\Order\Config::class,
            ['getVisibleOnFrontStatuses']
        );
        $this->orderCollection = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Collection::class,
            [
                'addAttributeToFilter',
                'addAttributeToSort',
                'setPage',
                'setOrders',
            ]
        );
        $this->stockRegistry = $this->getMockBuilder(\Magento\CatalogInventory\Model\StockRegistry::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem', '__wakeup'])
            ->getMock();

        $this->stockItemMock = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            ['getIsInStock', '__wakeup']
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    protected function createBlockObject()
    {
        $this->block = $this->objectManagerHelper->getObject(
            \Magento\Sales\Block\Reorder\Sidebar::class,
            [
                'context' => $this->context,
                'orderCollectionFactory' => $this->orderCollectionFactory,
                'orderConfig' => $this->orderConfig,
                'customerSession' => $this->customerSession,
                'httpContext' => $this->httpContext,
                'stockRegistry' => $this->stockRegistry,
            ]
        );
    }

    public function testGetIdentities()
    {
        $websiteId = 1;
        $storeId = null;
        $productTags = ['catalog_product_1'];
        $limit = 5;

        $storeManager = $this->createPartialMock(\Magento\Store\Model\StoreManager::class, ['getStore']);
        $this->context->expects($this->once())
            ->method('getStoreManager')
            ->will($this->returnValue($storeManager));

        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId']);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->will($this->returnValue($websiteId));
        $storeManager->expects($this->once())
            ->method('getStore')
            ->with($this->equalTo($storeId))
            ->will($this->returnValue($store));

        $product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['__wakeUp', 'getIdentities', 'getWebsiteIds']
        );
        $product->expects($this->once())
            ->method('getIdentities')
            ->will($this->returnValue($productTags));
        $product->expects($this->atLeastOnce())
            ->method('getWebsiteIds')
            ->will($this->returnValue([$websiteId]));

        $item = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Item::class,
            ['__wakeup', 'getProduct']
        );
        $item->expects($this->atLeastOnce())
            ->method('getProduct')
            ->will($this->returnValue($product));

        $order = $this->createPartialMock(
            \Magento\Sales\Model\Order::class,
            ['__wakeup', 'getParentItemsRandomCollection']
        );
        $order->expects($this->atLeastOnce())
            ->method('getParentItemsRandomCollection')
            ->with($this->equalTo($limit))
            ->will($this->returnValue([$item]));

        $this->createBlockObject();
        $this->assertSame($this->block, $this->block->setOrders([$order]));
        $this->assertEquals($productTags, $this->block->getIdentities());
    }

    public function testInitOrders()
    {
        $customerId = 25;
        $attribute = ['customer_id', 'status'];

        $this->httpContext->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo(Context::CONTEXT_AUTH))
            ->will($this->returnValue(true));

        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue($customerId));

        $statuses = ['pending', 'processing', 'complete'];
        $this->orderConfig->expects($this->once())
            ->method('getVisibleOnFrontStatuses')
            ->will($this->returnValue($statuses));

        $this->orderCollection->expects($this->at(0))
            ->method('addAttributeToFilter')
            ->with(
                $attribute[0],
                $this->equalTo($customerId)
            )
            ->will($this->returnSelf());
        $this->orderCollection->expects($this->at(1))
            ->method('addAttributeToFilter')
            ->with($attribute[1], $this->equalTo(['in' => $statuses]))
            ->will($this->returnSelf());
        $this->orderCollection->expects($this->at(2))
            ->method('addAttributeToSort')
            ->with('created_at', 'desc')
            ->will($this->returnSelf());
        $this->orderCollection->expects($this->at(3))
            ->method('setPage')
            ->with($this->equalTo(1), $this->equalTo(1))
            ->will($this->returnSelf());

        $this->orderCollectionFactory->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($this->orderCollection));
        $this->createBlockObject();
        $this->assertEquals($this->orderCollection, $this->block->getOrders());
    }

    public function testIsItemAvailableForReorder()
    {
        $productId = 1;
        $result = true;
        $product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getId', '__wakeup']);
        $product->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($productId));
        $this->stockItemMock->expects($this->once())
            ->method('getIsInStock')
            ->will($this->returnValue($result));
        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $orderItem = $this->createPartialMock(\Magento\Sales\Model\Order\Item::class, ['getStore', 'getProduct']);
        $orderItem->expects($this->any())
            ->method('getProduct')
            ->will($this->returnValue($product));
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId']);
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(10));
        $orderItem->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $this->createBlockObject();
        $this->assertSame($result, $this->block->isItemAvailableForReorder($orderItem));
    }

    public function testItemNotAvailableForReorderWhenProductNotExist()
    {
        $this->stockItemMock->expects($this->never())->method('getIsInStock');
        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $orderItem = $this->createMock(\Magento\Sales\Model\Order\Item::class);
        $orderItem->expects($this->any())
            ->method('getProduct')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->createBlockObject();
        $this->assertSame(false, $this->block->isItemAvailableForReorder($orderItem));
    }
}
