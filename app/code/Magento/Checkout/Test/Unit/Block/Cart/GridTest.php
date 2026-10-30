<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Cart;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Block\Cart\Grid;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Pager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GridTest extends TestCase
{
    /**
     * @var Grid
     */
    private $block;

    /**
     * @var MockObject
     */
    private $itemCollectionFactoryMock;

    /**
     * @var MockObject
     */
    private $joinAttributeProcessorMock;

    /**
     * @var MockObject
     */
    private $scopeConfigMock;

    /**
     * @var MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var MockObject
     */
    private $itemCollectionMock;

    /**
     * @var MockObject
     */
    private $quoteMock;

    /**
     * @var MockObject
     */
    private $layoutMock;

    /**
     * @var MockObject
     */
    private $pagerBlockMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $objectManagerHelper->prepareObjectManager();
        $this->itemCollectionFactoryMock =
            $this->getMockBuilder(CollectionFactory::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['create'])
                ->getMock();
        $this->joinAttributeProcessorMock =
            $this->createMock(JoinProcessorInterface::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->checkoutSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemCollectionMock = $objectManagerHelper
            ->getCollectionMock(Collection::class, []);
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->onlyMethods(['getAllVisibleItems', 'getAllItems', 'getItemsCount'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->createMock(LayoutInterface::class);
        $this->pagerBlockMock = $this->getMockBuilder(Pager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSessionMock->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->method('getAllVisibleItems')->willReturn([]);
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->method('getId')->willReturn(1);
        $storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $storeManagerMock->method('getStore')->willReturn($storeMock);
        $this->block = $objectManagerHelper->getObject(
            Grid::class,
            [
                'itemCollectionFactory' => $this->itemCollectionFactoryMock,
                'joinAttributeProcessor' => $this->joinAttributeProcessorMock,
                'scopeConfig' => $this->scopeConfigMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'layout' => $this->layoutMock,
                'storeManager' => $storeManagerMock,
                'data' => ['template' => 'cart/form1.phtml']
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetTemplate(): void
    {
        $this->assertEquals('cart/form1.phtml', $this->block->getTemplate());
    }

    /**
     * @return void
     */
    public function testGetItemsForGrid(): void
    {
        $this->getMockItemsForGrid();
        $this->assertEquals($this->itemCollectionMock, $this->block->getItemsForGrid());
    }

    /**
     * @return void
     * @cover \Magento\Checkout\Block\Cart\Grid::_prepareLayout
     */
    public function testSetLayout(): void
    {
        $itemsCount = 150;
        $availableLimit = 20;
        $this->getMockItemsForGrid();
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn($itemsCount);
        $this->scopeConfigMock
            ->method('getValue')
            ->willReturnCallback(fn($operation) => match ([$operation]) {
                [Grid::XPATH_CONFIG_NUMBER_ITEMS_TO_DISPLAY_PAGER] => 20,
                [Grid::XPATH_CONFIG_NUMBER_ITEMS_TO_DISPLAY_PAGER] => $availableLimit
            });
        $this->layoutMock
            ->expects($this->once())
            ->method('createBlock')
            ->with(Pager::class)
            ->willReturn($this->pagerBlockMock);
        $this->pagerBlockMock
            ->expects($this->once())
            ->method('setAvailableLimit')
            ->with([$availableLimit => $availableLimit])
            ->willReturnSelf();
        $this->pagerBlockMock
            ->expects($this->once())
            ->method('setCollection')
            ->with($this->itemCollectionMock)
            ->willReturnSelf();
        $this->layoutMock->expects($this->once())->method('setChild')->with(null, null, 'pager');
        $this->itemCollectionMock->expects($this->once())->method('load')->willReturnSelf();
        $this->quoteMock->expects($this->never())->method('getAllVisibleItems');
        $this->itemCollectionMock->expects($this->atLeastOnce())->method('getItems')->willReturn([]);
        $this->block->setLayout($this->layoutMock);
    }

    /**
     * @return void
     */
    public function testSetLayoutAssignsQuoteChildItemsToPagedParents(): void
    {
        $child = $this->getMockBuilder(Item::class)->disableOriginalConstructor()->getMock();
        $quoteParent = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getChildren'])
            ->getMock();
        $quoteParent->setId(1);
        $quoteParent->method('getChildren')->willReturn([$child]);
        $quoteChild = $this->getMockBuilder(Item::class)->disableOriginalConstructor()->getMock();
        $quoteChild->setId(2);
        $otherQuoteItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getChildren'])
            ->getMock();
        $otherQuoteItem->setId(3);
        $otherQuoteItem->expects($this->never())->method('getChildren');

        $pageParent = $this->createPageItem(1, []);
        $pageParent->expects($this->once())->method('addChild')->with($child);
        $pageWithoutQuoteItem = $this->createPageItem(99, []);
        $pageWithoutQuoteItem->expects($this->never())->method('addChild');

        $this->quoteMock->method('getAllItems')->willReturn([$quoteParent, $quoteChild, $otherQuoteItem]);
        $this->preparePagedLayout([1 => $pageParent, 99 => $pageWithoutQuoteItem]);

        $this->block->setLayout($this->layoutMock);
    }

    /**
     * @return void
     */
    public function testSetLayoutDoesNotAssignChildItemsTwice(): void
    {
        $pageParent = $this->createPageItem(1, [$this->createMock(Item::class)]);
        $pageParent->expects($this->never())->method('addChild');
        $quoteParent = $this->getMockBuilder(Item::class)->disableOriginalConstructor()->getMock();
        $quoteParent->setId(1);

        $this->quoteMock->method('getAllItems')->willReturn([$quoteParent]);
        $this->preparePagedLayout([1 => $pageParent]);

        $this->block->setLayout($this->layoutMock);
    }

    /**
     * @return void
     */
    public function testSetLayoutDoesNotReadQuoteItemsForEmptyPage(): void
    {
        $this->quoteMock->expects($this->never())->method('getAllItems');
        $this->preparePagedLayout([]);

        $this->block->setLayout($this->layoutMock);
    }

    /**
     * @param int $id
     * @param array $children
     * @return MockObject
     */
    private function createPageItem(int $id, array $children): MockObject
    {
        $productMock = $this->createMock(Product::class);
        $productMock->method('isVisibleInSiteVisibility')->willReturn(true);
        $item = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addChild', 'getChildren', 'getProduct'])
            ->getMock();
        $item->setId($id);
        $item->setData('store_id', 1);
        $item->method('getChildren')->willReturn($children);
        $item->method('getProduct')->willReturn($productMock);
        return $item;
    }

    /**
     * @param array $pageItems
     * @return void
     */
    private function preparePagedLayout(array $pageItems): void
    {
        $this->itemCollectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->itemCollectionMock);
        $this->itemCollectionMock->method('setQuote')->willReturnSelf();
        $this->itemCollectionMock->method('addFieldToFilter')->willReturnSelf();
        $this->itemCollectionMock->method('getItems')->willReturn($pageItems);
        $this->quoteMock->method('getItemsCount')->willReturn(150);
        $this->scopeConfigMock->method('getValue')->willReturn(20);
        $this->pagerBlockMock->method('setAvailableLimit')->willReturnSelf();
        $this->pagerBlockMock->method('setCollection')->willReturnSelf();
        $this->layoutMock->method('createBlock')->with(Pager::class)->willReturn($this->pagerBlockMock);
    }

    /**
     * @return void
     */
    public function testGetItems(): void
    {
        $this->getMockItemsForGrid();
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(20);
        $this->itemCollectionMock->expects($this->once())->method('getItems')->willReturn(['expected']);
        $this->assertEquals(['expected'], $this->block->getItems());
    }

    /**
     * @return void
     */
    private function getMockItemsForGrid(): void
    {
        $this->itemCollectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->itemCollectionMock);
        $this->checkoutSessionMock->method('getQuote')->willReturn($this->quoteMock);
        $this->itemCollectionMock->expects($this->once())->method('setQuote')->with($this->quoteMock)->willReturnSelf();
        $this->itemCollectionMock
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with('parent_item_id', ['null' => true])
            ->willReturnSelf();
        $this->joinAttributeProcessorMock->expects($this->once())->method('process')->with($this->itemCollectionMock);
    }

    /**
     * @return void
     * @cover \Magento\Checkout\Block\Cart::prepareItemUrls
     */
    public function testGetItemsIfCustomItemsExists(): void
    {
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeMock = $this->createMock(StoreInterface::class);
        $storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $objectManagerHelper->getObject(
            Grid::class,
            [
                'itemCollectionFactory' => $this->itemCollectionFactoryMock,
                'joinAttributeProcessor' => $this->joinAttributeProcessorMock,
                'scopeConfig' => $this->scopeConfigMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'layout' => $this->layoutMock,
                'data' => ['custom_items' => [$itemMock]],
                'storeManager' => $storeManager
            ]
        );
        $this->assertEquals([$itemMock], $this->block->getItems());
    }

    /**
     * @return void
     */
    public function testGetItemsWhenPagerNotVisible(): void
    {
        $this->assertEquals([], $this->block->getItems());
    }
}
