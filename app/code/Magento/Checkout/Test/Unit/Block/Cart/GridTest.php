<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Unit\Block\Cart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Checkout\Block\Cart\Grid
     */
    private $block;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $itemCollectionFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $joinAttributeProcessorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $itemCollectionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $quoteMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $layoutMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $pagerBlockMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->itemCollectionFactoryMock =
            $this->getMockBuilder(\Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $this->joinAttributeProcessorMock =
            $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class)
                ->getMockForAbstractClass();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->checkoutSessionMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemCollectionMock = $objectManagerHelper
            ->getCollectionMock(\Magento\Quote\Model\ResourceModel\Quote\Item\Collection::class, []);
        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMockForAbstractClass();
        $this->pagerBlockMock = $this->getMockBuilder(\Magento\Theme\Block\Html\Pager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->checkoutSessionMock->expects($this->any())->method('getQuote')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->any())->method('getAllVisibleItems')->willReturn([]);
        $this->scopeConfigMock->expects($this->at(0))
            ->method('getValue')
            ->with(
                \Magento\Checkout\Block\Cart\Grid::XPATH_CONFIG_NUMBER_ITEMS_TO_DISPLAY_PAGER,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )->willReturn(20);
        $this->block = $objectManagerHelper->getObject(
            \Magento\Checkout\Block\Cart\Grid::class,
            [
                'itemCollectionFactory' => $this->itemCollectionFactoryMock,
                'joinAttributeProcessor' => $this->joinAttributeProcessorMock,
                'scopeConfig' => $this->scopeConfigMock,
                'checkoutSession' => $this->checkoutSessionMock,
                'layout' => $this->layoutMock,
                'data' => ['template' => 'cart/form1.phtml']
            ]
        );
    }

    public function testGetTemplate()
    {
        $this->assertEquals('cart/form1.phtml', $this->block->getTemplate());
    }

    public function testGetItemsForGrid()
    {
        $this->getMockItemsForGrid();
        $this->assertEquals($this->itemCollectionMock, $this->block->getItemsForGrid());
    }

    /**
     * @cover \Magento\Checkout\Block\Cart\Grid::_prepareLayout
     */
    public function testSetLayout()
    {
        $itemsCount = 150;
        $availableLimit = 20;
        $this->getMockItemsForGrid();
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn($itemsCount);
        $this->scopeConfigMock->expects($this->at(1))
            ->method('getValue')
            ->with(
                \Magento\Checkout\Block\Cart\Grid::XPATH_CONFIG_NUMBER_ITEMS_TO_DISPLAY_PAGER,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )->willReturn($availableLimit);
        $this->layoutMock
            ->expects($this->once())
            ->method('createBlock')
            ->with(\Magento\Theme\Block\Html\Pager::class)
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
        $this->itemCollectionMock->expects($this->once())->method('getItems')->willReturn([]);
        $this->block->setLayout($this->layoutMock);
    }

    public function testGetItems()
    {
        $this->getMockItemsForGrid();
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(20);
        $this->itemCollectionMock->expects($this->once())->method('getItems')->willReturn(['expected']);
        $this->assertEquals(['expected'], $this->block->getItems());
    }

    private function getMockItemsForGrid()
    {
        $this->itemCollectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->itemCollectionMock);
        $this->checkoutSessionMock->expects($this->any())->method('getQuote')->willReturn($this->quoteMock);
        $this->itemCollectionMock->expects($this->once())->method('setQuote')->with($this->quoteMock)->willReturnSelf();
        $this->itemCollectionMock
            ->expects($this->once())
            ->method('addFieldToFilter')
            ->with('parent_item_id', ['null' => true])
            ->willReturnSelf();
        $this->joinAttributeProcessorMock->expects($this->once())->method('process')->with($this->itemCollectionMock);
    }

    /**
     * @cover \Magento\Checkout\Block\Cart::prepareItemUrls
     */
    public function testGetItemsIfCustomItemsExists()
    {
        $itemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->getMockForAbstractClass();
        $storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $objectManagerHelper->getObject(
            \Magento\Checkout\Block\Cart\Grid::class,
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

    public function testGetItemsWhenPagerNotVisible()
    {
        $this->assertEquals([], $this->block->getItems());
    }
}
