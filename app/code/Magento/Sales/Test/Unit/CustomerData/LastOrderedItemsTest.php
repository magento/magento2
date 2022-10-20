<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\CustomerData;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\CustomerData\LastOrderedItems;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LastOrderedItemsTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $orderCollectionFactoryMock;

    /**
     * @var MockObject
     */
    private $orderConfigMock;

    /**
     * @var MockObject
     */
    private $customerSessionMock;

    /**
     * @var MockObject
     */
    private $stockRegistryMock;

    /**
     * @var MockObject
     */
    private $storeManagerMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var MockObject
     */
    private $orderMock;

    /**
     * @var MockObject
     */
    private $productRepositoryMock;

    /**
     * @var LastOrderedItems
     */
    private $section;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->orderCollectionFactoryMock =
            $this->getMockBuilder(CollectionFactory::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['create'])
                ->getMock();
        $this->orderConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockRegistryMock = $this->getMockBuilder(StockRegistryInterface::class)
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $this->section = new LastOrderedItems(
            $this->orderCollectionFactoryMock,
            $this->orderConfigMock,
            $this->customerSessionMock,
            $this->stockRegistryMock,
            $this->storeManagerMock,
            $this->productRepositoryMock,
            $this->loggerMock
        );
    }

    /**
     * @return void
     */
    public function testGetSectionData(): void
    {
        $storeId = 1;
        $websiteId = 4;
        $expectedItem1 = [
            'id' => 1,
            'name' => 'Product Name 1',
            'url' => 'http://example.com',
            'is_saleable' => true
        ];
        $expectedItem2 = [
            'id' => 2,
            'name' => 'Product Name 2',
            'url' => null,
            'is_saleable' => true
        ];
        $productIdVisible = 1;
        $productIdNotVisible = 2;
        $stockItemMock = $this->getMockBuilder(StockItemInterface::class)
            ->getMockForAbstractClass();
        $itemWithVisibleProduct = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemWithNotVisibleProduct = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productVisible = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productNotVisible = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $items = [$itemWithVisibleProduct, $itemWithNotVisibleProduct];
        $this->getLastOrderMock();
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->any())->method('getWebsiteId')->willReturn($websiteId);
        $storeMock->expects($this->any())->method('getId')->willReturn($storeId);
        $this->orderMock->expects($this->once())
            ->method('getParentItemsRandomCollection')
            ->with(LastOrderedItems::SIDEBAR_ORDER_LIMIT)
            ->willReturn($items);
        $productVisible->expects($this->once())->method('isVisibleInSiteVisibility')->willReturn(true);
        $productVisible->expects($this->once())->method('getProductUrl')->willReturn($expectedItem1['url']);
        $productVisible->expects($this->once())->method('getWebsiteIds')->willReturn([1, 4]);
        $productVisible->expects($this->once())->method('getId')->willReturn($productIdVisible);
        $productNotVisible->expects($this->once())->method('isVisibleInSiteVisibility')->willReturn(false);
        $productNotVisible->expects($this->never())->method('getProductUrl');
        $productNotVisible->expects($this->once())->method('getWebsiteIds')->willReturn([1, 4]);
        $productNotVisible->expects($this->once())->method('getId')->willReturn($productIdNotVisible);
        $itemWithVisibleProduct->expects($this->once())->method('getProductId')->willReturn($productIdVisible);
        $itemWithVisibleProduct->expects($this->once())->method('getProduct')->willReturn($productVisible);
        $itemWithVisibleProduct->expects($this->once())->method('getId')->willReturn($expectedItem1['id']);
        $itemWithVisibleProduct->expects($this->once())->method('getName')->willReturn($expectedItem1['name']);
        $itemWithVisibleProduct->expects($this->once())->method('getStore')->willReturn($storeMock);
        $itemWithNotVisibleProduct->expects($this->once())->method('getProductId')->willReturn($productIdNotVisible);
        $itemWithNotVisibleProduct->expects($this->once())->method('getProduct')->willReturn($productNotVisible);
        $itemWithNotVisibleProduct->expects($this->once())->method('getId')->willReturn($expectedItem2['id']);
        $itemWithNotVisibleProduct->expects($this->once())->method('getName')->willReturn($expectedItem2['name']);
        $itemWithNotVisibleProduct->expects($this->once())->method('getStore')->willReturn($storeMock);
        $this->productRepositoryMock->expects($this->any())
            ->method('getById')
            ->willReturnMap([
                [$productIdVisible, false, $storeId, false, $productVisible],
                [$productIdNotVisible, false, $storeId, false, $productNotVisible]
            ]);
        $this->stockRegistryMock
            ->expects($this->any())
            ->method('getStockItem')
            ->willReturnMap([
                [$productIdVisible, $websiteId, $stockItemMock],
                [$productIdNotVisible, $websiteId, $stockItemMock]
            ]);
        $stockItemMock->expects($this->exactly(2))->method('getIsInStock')->willReturn($expectedItem1['is_saleable']);
        $this->assertEquals(['items' => [$expectedItem1, $expectedItem2]], $this->section->getSectionData());
    }

    /**
     * @return MockObject
     */
    private function getLastOrderMock(): MockObject
    {
        $customerId = 1;
        $visibleOnFrontStatuses = ['complete'];
        $orderCollectionMock = $this->objectManagerHelper
            ->getCollectionMock(Collection::class, [$this->orderMock]);
        $this->customerSessionMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->orderConfigMock
            ->expects($this->once())
            ->method('getVisibleOnFrontStatuses')
            ->willReturn($visibleOnFrontStatuses);
        $this->orderCollectionFactoryMock->expects($this->once())->method('create')->willReturn($orderCollectionMock);
        $orderCollectionMock->method('addAttributeToFilter')
            ->withConsecutive(
                ['customer_id', $customerId],
                ['status', ['in' => $visibleOnFrontStatuses]]
            )->willReturnOnConsecutiveCalls($orderCollectionMock, $orderCollectionMock);
        $orderCollectionMock->expects($this->once())
            ->method('addAttributeToSort')
            ->willReturnSelf();
        $orderCollectionMock->expects($this->once())
            ->method('setPage')
            ->willReturnSelf();
        return $this->orderMock;
    }

    /**
     * @return void
     */
    public function testGetSectionDataWithNotExistingProduct(): void
    {
        $storeId = 1;
        $websiteId = 4;
        $productId = 1;
        $exception = new NoSuchEntityException(__("Product doesn't exist"));
        $orderItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProductId'])
            ->getMock();
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMockForAbstractClass();

        $this->getLastOrderMock();
        $this->storeManagerMock->expects($this->exactly(2))->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);
        $storeMock->expects($this->once())->method('getId')->willReturn($storeId);
        $this->orderMock->expects($this->once())
            ->method('getParentItemsRandomCollection')
            ->with(LastOrderedItems::SIDEBAR_ORDER_LIMIT)
            ->willReturn([$orderItemMock]);
        $orderItemMock->expects($this->once())->method('getProductId')->willReturn($productId);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId, false, $storeId)
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())->method('critical')->with($exception);

        $this->assertEquals(['items' => []], $this->section->getSectionData());
    }
}
