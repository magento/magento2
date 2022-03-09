<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Reorder;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Customer\Model\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Block\Reorder\Sidebar;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SidebarTest extends TestCase
{
    /**
     * @var Sidebar|MockObject
     */
    private $block;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|MockObject
     */
    private $context;

    /**
     * @var CollectionFactory|MockObject
     */
    private $orderCollectionFactory;

    /**
     * @var Session|MockObject
     */
    private $customerSession;

    /**
     * @var Config|MockObject
     */
    private $orderConfig;

    /**
     * @var \Magento\Framework\App\Http\Context|MockObject
     */
    private $httpContext;

    /**
     * @var Collection|MockObject
     */
    private $orderCollection;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /** @var MockObject */
    private $stockItemMock;

    /**
     * @var MockObject
     */
    private $stockRegistry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->markTestIncomplete('MAGETWO-36789');
        $this->objectManagerHelper = new ObjectManager($this);
        $this->context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->httpContext = $this->createPartialMock(\Magento\Framework\App\Http\Context::class, ['getValue']);
        $this->orderCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->customerSession = $this->createPartialMock(Session::class, ['getCustomerId']);
        $this->orderConfig = $this->createPartialMock(
            Config::class,
            ['getVisibleOnFrontStatuses']
        );
        $this->orderCollection = $this->getMockBuilder(Collection::class)
            ->addMethods(['setOrders'])
            ->onlyMethods(['addAttributeToFilter', 'addAttributeToSort', 'setPage'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockRegistry = $this->getMockBuilder(StockRegistry::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStockItem'])
            ->getMock();

        $this->stockItemMock = $this->createPartialMock(
            Item::class,
            ['getIsInStock']
        );

        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->block = null;
    }

    /**
     * @return void
     */
    protected function createBlockObject(): void
    {
        $this->block = $this->objectManagerHelper->getObject(
            Sidebar::class,
            [
                'context' => $this->context,
                'orderCollectionFactory' => $this->orderCollectionFactory,
                'orderConfig' => $this->orderConfig,
                'customerSession' => $this->customerSession,
                'httpContext' => $this->httpContext,
                'stockRegistry' => $this->stockRegistry
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetIdentities(): void
    {
        $websiteId = 1;
        $storeId = null;
        $productTags = ['catalog_product_1'];
        $limit = 5;

        $storeManager = $this->createPartialMock(StoreManager::class, ['getStore']);
        $this->context->expects($this->once())
            ->method('getStoreManager')
            ->willReturn($storeManager);

        $store = $this->createPartialMock(Store::class, ['getWebsiteId']);
        $store->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $storeManager->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($store);

        $product = $this->createPartialMock(
            Product::class,
            ['getIdentities', 'getWebsiteIds']
        );
        $product->expects($this->once())
            ->method('getIdentities')
            ->willReturn($productTags);
        $product->expects($this->atLeastOnce())
            ->method('getWebsiteIds')
            ->willReturn([$websiteId]);

        $item = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Item::class)
            ->addMethods(['getProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        $item->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($product);

        $order = $this->createPartialMock(
            Order::class,
            ['getParentItemsRandomCollection']
        );
        $order->expects($this->atLeastOnce())
            ->method('getParentItemsRandomCollection')
            ->with($limit)
            ->willReturn([$item]);

        $this->createBlockObject();
        $this->assertSame($this->block, $this->block->setOrders([$order]));
        $this->assertEquals($productTags, $this->block->getIdentities());
    }

    /**
     * @return void
     */
    public function testInitOrders(): void
    {
        $customerId = 25;
        $attribute = ['customer_id', 'status'];

        $this->httpContext->expects($this->once())
            ->method('getValue')
            ->with(Context::CONTEXT_AUTH)
            ->willReturn(true);

        $this->customerSession->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $statuses = ['pending', 'processing', 'complete'];
        $this->orderConfig->expects($this->once())
            ->method('getVisibleOnFrontStatuses')
            ->willReturn($statuses);

        $this->orderCollection->method('addAttributeToFilter')
            ->withConsecutive([$attribute[0], $customerId], [$attribute[1], ['in' => $statuses]])
            ->willReturnOnConsecutiveCalls($this->orderCollection, $this->orderCollection);
        $this->orderCollection->method('setPage')
            ->with(1, 1)
            ->willReturn($this->orderCollection);
        $this->orderCollection->method('addAttributeToSort')
            ->with('created_at', 'desc')
            ->willReturn($this->orderCollection);

        $this->orderCollectionFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->orderCollection);
        $this->createBlockObject();
        $this->assertEquals($this->orderCollection, $this->block->getOrders());
    }

    /**
     * @return void
     */
    public function testIsItemAvailableForReorder(): void
    {
        $productId = 1;
        $result = true;
        $product = $this->createPartialMock(Product::class, ['getId']);
        $product->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $this->stockItemMock->expects($this->once())
            ->method('getIsInStock')
            ->willReturn($result);
        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $orderItem = $this->createPartialMock(Order\Item::class, ['getStore', 'getProduct']);
        $orderItem->expects($this->any())
            ->method('getProduct')
            ->willReturn($product);
        $store = $this->createPartialMock(Store::class, ['getWebsiteId']);
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(10);
        $orderItem->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $this->createBlockObject();
        $this->assertSame($result, $this->block->isItemAvailableForReorder($orderItem));
    }

    /**
     * @return void
     */
    public function testItemNotAvailableForReorderWhenProductNotExist(): void
    {
        $this->stockItemMock->expects($this->never())->method('getIsInStock');
        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->willReturn($this->stockItemMock);

        $orderItem = $this->createMock(Order\Item::class);
        $orderItem->expects($this->any())
            ->method('getProduct')
            ->willThrowException(new NoSuchEntityException());
        $this->createBlockObject();
        $this->assertFalse($this->block->isItemAvailableForReorder($orderItem));
    }
}
