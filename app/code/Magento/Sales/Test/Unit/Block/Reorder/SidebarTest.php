<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Reorder;

use Magento\Customer\Model\Context;

/**
 * Class SidebarTest
 *
 * @package Magento\Sales\Block\Reorder
 */
class SidebarTest extends \PHPUnit_Framework_TestCase
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
        $this->context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->httpContext = $this->getMock('Magento\Framework\App\Http\Context', ['getValue'], [], '', false);
        $this->orderCollectionFactory = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Order\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->customerSession = $this->getMock(
            'Magento\Customer\Model\Session',
            ['getCustomerId'],
            [],
            '',
            false
        );
        $this->orderConfig = $this->getMock(
            'Magento\Sales\Model\Order\Config',
            ['getVisibleOnFrontStatuses'],
            [],
            '',
            false
        );
        $this->orderCollection = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Order\Collection',
            [
                'addAttributeToFilter',
                'addAttributeToSort',
                'setPage',
                'setOrders',
            ],
            [],
            '',
            false
        );
        $this->stockRegistry = $this->getMockBuilder('Magento\CatalogInventory\Model\StockRegistry')
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem', '__wakeup'])
            ->getMock();

        $this->stockItemMock = $this->getMock(
            'Magento\CatalogInventory\Model\Stock\Item',
            ['getIsInStock', '__wakeup'],
            [],
            '',
            false
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
            'Magento\Sales\Block\Reorder\Sidebar',
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

        $storeManager = $this->getMock('Magento\Store\Model\StoreManager', ['getStore'], [], '', false);
        $this->context->expects($this->once())
            ->method('getStoreManager')
            ->will($this->returnValue($storeManager));

        $store = $this->getMock('Magento\Store\Model', ['getWebsiteId'], [], '', false);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->will($this->returnValue($websiteId));
        $storeManager->expects($this->once())
            ->method('getStore')
            ->with($this->equalTo($storeId))
            ->will($this->returnValue($store));

        $product = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['__wakeUp', 'getIdentities', 'getWebsiteIds'],
            [],
            '',
            false
        );
        $product->expects($this->once())
            ->method('getIdentities')
            ->will($this->returnValue($productTags));
        $product->expects($this->atLeastOnce())
            ->method('getWebsiteIds')
            ->will($this->returnValue([$websiteId]));

        $item = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Order\Item',
            ['__wakeup', 'getProduct'],
            [],
            '',
            false
        );
        $item->expects($this->atLeastOnce())
            ->method('getProduct')
            ->will($this->returnValue($product));

        $order = $this->getMock(
            'Magento\Sales\Model\Order',
            ['__wakeup', 'getParentItemsRandomCollection'],
            [],
            '',
            false
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
        $product = $this->getMock('Magento\Catalog\Model\Product', ['getId', '__wakeup'], [], '', false);
        $product->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($productId));
        $this->stockItemMock->expects($this->once())
            ->method('getIsInStock')
            ->will($this->returnValue($result));
        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));

        $orderItem = $this->getMock('Magento\Sales\Model\Order\Item', ['getStore', 'getProduct'], [], '', false);
        $orderItem->expects($this->any())
            ->method('getProduct')
            ->will($this->returnValue($product));
        $store = $this->getMock('Magento\Store\Model\Store', ['getWebsiteId'], [], '', false);
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

        $orderItem = $this->getMock('Magento\Sales\Model\Order\Item', [], [], '', false);
        $orderItem->expects($this->any())
            ->method('getProduct')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->createBlockObject();
        $this->assertSame(false, $this->block->isItemAvailableForReorder($orderItem));
    }
}
