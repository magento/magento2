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
     * @var \Magento\Sales\Block\Reorder\Sidebar|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $block;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderConfig;

    /**
     * @var \Magento\Framework\App\Http\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $httpContext;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderCollection;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $stockItemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $stockRegistry;

    protected function setUp(): void
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
            ->willReturn($this->stockItemMock);
    }

    protected function tearDown(): void
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
            ->willReturn($storeManager);

        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId']);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $storeManager->expects($this->once())
            ->method('getStore')
            ->with($this->equalTo($storeId))
            ->willReturn($store);

        $product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['__wakeUp', 'getIdentities', 'getWebsiteIds']
        );
        $product->expects($this->once())
            ->method('getIdentities')
            ->willReturn($productTags);
        $product->expects($this->atLeastOnce())
            ->method('getWebsiteIds')
            ->willReturn([$websiteId]);

        $item = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Item::class,
            ['__wakeup', 'getProduct']
        );
        $item->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($product);

        $order = $this->createPartialMock(
            \Magento\Sales\Model\Order::class,
            ['__wakeup', 'getParentItemsRandomCollection']
        );
        $order->expects($this->atLeastOnce())
            ->method('getParentItemsRandomCollection')
            ->with($this->equalTo($limit))
            ->willReturn([$item]);

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
            ->willReturn(true);

        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $statuses = ['pending', 'processing', 'complete'];
        $this->orderConfig->expects($this->once())
            ->method('getVisibleOnFrontStatuses')
            ->willReturn($statuses);

        $this->orderCollection->expects($this->at(0))
            ->method('addAttributeToFilter')
            ->with(
                $attribute[0],
                $this->equalTo($customerId)
            )
            ->willReturnSelf();
        $this->orderCollection->expects($this->at(1))
            ->method('addAttributeToFilter')
            ->with($attribute[1], $this->equalTo(['in' => $statuses]))
            ->willReturnSelf();
        $this->orderCollection->expects($this->at(2))
            ->method('addAttributeToSort')
            ->with('created_at', 'desc')
            ->willReturnSelf();
        $this->orderCollection->expects($this->at(3))
            ->method('setPage')
            ->with($this->equalTo(1), $this->equalTo(1))
            ->willReturnSelf();

        $this->orderCollectionFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->orderCollection);
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
            ->willReturn($productId);
        $this->stockItemMock->expects($this->once())
            ->method('getIsInStock')
            ->willReturn($result);
        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $orderItem = $this->createPartialMock(\Magento\Sales\Model\Order\Item::class, ['getStore', 'getProduct']);
        $orderItem->expects($this->any())
            ->method('getProduct')
            ->willReturn($product);
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId']);
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(10);
        $orderItem->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $this->createBlockObject();
        $this->assertSame($result, $this->block->isItemAvailableForReorder($orderItem));
    }

    public function testItemNotAvailableForReorderWhenProductNotExist()
    {
        $this->stockItemMock->expects($this->never())->method('getIsInStock');
        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $orderItem = $this->createMock(\Magento\Sales\Model\Order\Item::class);
        $orderItem->expects($this->any())
            ->method('getProduct')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->createBlockObject();
        $this->assertFalse($this->block->isItemAvailableForReorder($orderItem));
    }
}
