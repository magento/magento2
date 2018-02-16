<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\CustomerData;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for \Magento\Sales\CustomerData\LastOrderedItems Class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LastOrderedItemsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $stockRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Sales\CustomerData\LastOrderedItems
     */
    private $section;

    protected function setUp()
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
        
        $this->section = new \Magento\Sales\CustomerData\LastOrderedItems(
            $this->orderCollectionFactoryMock,
            $this->orderConfigMock,
            $this->customerSessionMock,
            $this->stockRegistryMock,
            $this->storeManagerMock
        );
    }

    /**
     * @covers \Magento\Sales\CustomerData\LastOrderedItems
     *
     * @return void
     */
    public function testGetSectionData()
    {
        $websiteId = 4;
        $expectedItem = [
            'id' => 1,
            'name' => 'Product Name',
            'url' => 'http://example.com',
            'is_saleable' => true,
        ];
        $productId = 10;

        $stockItemMock = $this->getMockBuilder(\Magento\CatalogInventory\Api\Data\StockItemInterface::class)
            ->getMockForAbstractClass();
        $itemWithProductMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $itemWithoutProductMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $items = [$itemWithoutProductMock, $itemWithProductMock];
        $this->getLastOrderMock();
        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->any())->method('getWebsiteId')->willReturn($websiteId);
        $this->orderMock->expects($this->once())
            ->method('getParentItemsRandomCollection')
            ->with(\Magento\Sales\CustomerData\LastOrderedItems::SIDEBAR_ORDER_LIMIT)
            ->willReturn($items);

        $itemWithProductMock->expects($this->once())->method('hasData')->with('product')->willReturn(true);
        $itemWithProductMock->expects($this->any())->method('getProduct')->willReturn($productMock);
        $productMock->expects($this->once())->method('getWebsiteIds')->willReturn([1, 4]);
        $itemWithProductMock->expects($this->once())->method('getId')->willReturn($expectedItem['id']);
        $itemWithProductMock->expects($this->once())->method('getName')->willReturn($expectedItem['name']);
        $productMock->expects($this->once())->method('getProductUrl')->willReturn($expectedItem['url']);
        $this->stockRegistryMock->expects($this->once())->method('getStockItem')->willReturn($stockItemMock);
        $productMock->expects($this->once())->method('getId')->willReturn($productId);
        $itemWithProductMock->expects($this->once())->method('getStore')->willReturn($storeMock);
        $this->stockRegistryMock
            ->expects($this->once())
            ->method('getStockItem')
            ->with($productId, $websiteId)
            ->willReturn($stockItemMock);
        $stockItemMock->expects($this->once())->method('getIsInStock')->willReturn($expectedItem['is_saleable']);
        $itemWithoutProductMock->expects($this->once())->method('hasData')->with('product')->willReturn(false);

        $this->assertEquals(['items' => [$expectedItem]], $this->section->getSectionData());
    }

    /**
     * Return last order mock object.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
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
}
