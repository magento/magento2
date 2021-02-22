<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\CustomerData;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LastOrderedItemsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $orderCollectionFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $orderConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerSessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $stockRegistryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $orderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $productRepositoryMock;

    /**
     * @var \Magento\Sales\CustomerData\LastOrderedItems
     */
    private $section;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->orderCollectionFactoryMock =
            $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\CollectionFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $this->orderConfigMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockRegistryMock = $this->getMockBuilder(\Magento\CatalogInventory\Api\StockRegistryInterface::class)
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepositoryMock = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->getMockForAbstractClass();

        $this->section = new \Magento\Sales\CustomerData\LastOrderedItems(
            $this->orderCollectionFactoryMock,
            $this->orderConfigMock,
            $this->customerSessionMock,
            $this->stockRegistryMock,
            $this->storeManagerMock,
            $this->productRepositoryMock,
            $this->loggerMock
        );
    }

    public function testGetSectionData()
    {
        $storeId = 1;
        $websiteId = 4;
        $expectedItem1 = [
            'id' => 1,
            'name' => 'Product Name 1',
            'url' => 'http://example.com',
            'is_saleable' => true,
        ];
        $expectedItem2 = [
            'id' => 2,
            'name' => 'Product Name 2',
            'url' => null,
            'is_saleable' => true,
        ];
        $productIdVisible = 1;
        $productIdNotVisible = 2;
        $stockItemMock = $this->getMockBuilder(\Magento\CatalogInventory\Api\Data\StockItemInterface::class)
            ->getMockForAbstractClass();
        $itemWithVisibleProduct = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemWithNotVisibleProduct = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productVisible = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productNotVisible = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $items = [$itemWithVisibleProduct, $itemWithNotVisibleProduct];
        $this->getLastOrderMock();
        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->any())->method('getWebsiteId')->willReturn($websiteId);
        $storeMock->expects($this->any())->method('getId')->willReturn($storeId);
        $this->orderMock->expects($this->once())
            ->method('getParentItemsRandomCollection')
            ->with(\Magento\Sales\CustomerData\LastOrderedItems::SIDEBAR_ORDER_LIMIT)
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
                [$productIdNotVisible, false, $storeId, false, $productNotVisible],
            ]);
        $this->stockRegistryMock
            ->expects($this->any())
            ->method('getStockItem')
            ->willReturnMap([
                [$productIdVisible, $websiteId, $stockItemMock],
                [$productIdNotVisible, $websiteId, $stockItemMock],
            ]);
        $stockItemMock->expects($this->exactly(2))->method('getIsInStock')->willReturn($expectedItem1['is_saleable']);
        $this->assertEquals(['items' => [$expectedItem1, $expectedItem2]], $this->section->getSectionData());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getLastOrderMock()
    {
        $customerId = 1;
        $visibleOnFrontStatuses = ['complete'];
        $orderCollectionMock = $this->objectManagerHelper
            ->getCollectionMock(\Magento\Sales\Model\ResourceModel\Order\Collection::class, [$this->orderMock]);
        $this->customerSessionMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->orderConfigMock
            ->expects($this->once())
            ->method('getVisibleOnFrontStatuses')
            ->willReturn($visibleOnFrontStatuses);
        $this->orderCollectionFactoryMock->expects($this->once())->method('create')->willReturn($orderCollectionMock);
        $orderCollectionMock->expects($this->at(0))
            ->method('addAttributeToFilter')
            ->with('customer_id', $customerId)
            ->willReturnSelf();
        $orderCollectionMock->expects($this->at(1))
            ->method('addAttributeToFilter')
            ->with('status', ['in' => $visibleOnFrontStatuses])
            ->willReturnSelf();
        $orderCollectionMock->expects($this->once())
            ->method('addAttributeToSort')
            ->willReturnSelf();
        $orderCollectionMock->expects($this->once())
            ->method('setPage')
            ->willReturnSelf();
        return $this->orderMock;
    }

    public function testGetSectionDataWithNotExistingProduct()
    {
        $storeId = 1;
        $websiteId = 4;
        $productId = 1;
        $exception = new \Magento\Framework\Exception\NoSuchEntityException(__("Product doesn't exist"));
        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductId'])
            ->getMock();
        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)->getMockForAbstractClass();

        $this->getLastOrderMock();
        $this->storeManagerMock->expects($this->exactly(2))->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);
        $storeMock->expects($this->once())->method('getId')->willReturn($storeId);
        $this->orderMock->expects($this->once())
            ->method('getParentItemsRandomCollection')
            ->with(\Magento\Sales\CustomerData\LastOrderedItems::SIDEBAR_ORDER_LIMIT)
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
