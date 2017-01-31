<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Order;

use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as ItemCollection;

class ItemsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $itemCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    /**
     * @var \Magento\Sales\Block\Order\Items
     */
    private $block;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->block = new \Magento\Sales\Block\Order\Items(
            $this->contextMock,
            $this->registryMock,
            [],
            $this->collectionFactoryMock
        );

        $this->itemCollectionMock = $this->getMockBuilder(ItemCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Common code for several tests to initialize block state with mocks
     *
     * @param int $collectionSize
     * @param bool $expectPager
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function prepareLayoutMocks($collectionSize, $expectPager)
    {
        $itemsPerPage = 42;
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->itemCollectionMock);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('sales/orders/items_per_page')
            ->willReturn($itemsPerPage);

        $pagerBlockName = 'BrilliantPager';
        $this->layoutMock->expects($this->atLeastOnce())
            ->method('getChildName')
            ->with(null, 'sales_order_item_pager')
            ->willReturn($pagerBlockName);
        $pagerBlockMock = $this->getMockBuilder(\Magento\Theme\Block\Html\Pager::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShowAmounts', 'setLimit', 'setCollection', 'setAvailableLimit', 'toHtml'])
            ->getMock();
        $this->layoutMock->expects($this->atLeastOnce())
            ->method('getBlock')
            ->with($pagerBlockName)
            ->willReturn($pagerBlockMock);
        $pagerBlockMock->expects($this->once())
            ->method('setLimit')
            ->with($itemsPerPage);
        $pagerBlockMock->expects($this->once())
            ->method('setCollection')
            ->with($this->itemCollectionMock);
        $pagerBlockMock->expects($this->once())
            ->method('setAvailableLimit')
            ->with([$itemsPerPage]);

        //isPagerDisplayed() call
        $this->itemCollectionMock->expects($this->atLeastOnce())
            ->method('getSize')
            ->willReturn($collectionSize);

        $pagerBlockMock->expects($this->once())
            ->method('setShowAmounts')
            ->with($expectPager);

        $this->block->setLayout($this->layoutMock);
        return $pagerBlockMock;
    }

    /**
     * Simple way to test protected _prepareLayout() method.
     */
    public function testPrepareLayout()
    {
        $this->prepareLayoutMocks(100, true);
    }

    /**
     * @param int $collectionSize
     * @param bool $expectedState
     * @dataProvider pagerStates
     */
    public function testIsPagerDisplayed($collectionSize, $expectedState)
    {
        $this->prepareLayoutMocks($collectionSize, $expectedState);
        $this->assertEquals($expectedState, $this->block->isPagerDisplayed());
    }

    /**
     * Data provider for testIsPagerDisplayed
     *
     * @return array
     */
    public function pagerStates()
    {
        return ([
            [100, true],
            [42, false],
        ]);
    }

    public function testGetItems()
    {
        $collectionItems = [3, 5, 7];
        $this->prepareLayoutMocks(100, true);
        $this->itemCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn($collectionItems);
        $this->assertEquals($collectionItems, $this->block->getItems());
    }

    public function testGetPagerHtml()
    {
        $html = 'some HTML code';
        $pagerBlockMock = $this->prepareLayoutMocks(100, true);
        $pagerBlockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($html);
        $this->assertEquals($html, $this->block->getPagerHtml());
    }

    public function testLayoutWithoutPager()
    {
        $itemsPerPage = 42;
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->itemCollectionMock);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('sales/orders/items_per_page')
            ->willReturn($itemsPerPage);

        $this->layoutMock->expects($this->atLeastOnce())
            ->method('getChildName')
            ->with(null, 'sales_order_item_pager')
            ->willReturn(false);
        $pagerBlockMock = $this->getMockBuilder(\Magento\Theme\Block\Html\Pager::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShowAmounts', 'setLimit', 'setCollection', 'setAvailableLimit', 'toHtml'])
            ->getMock();
        $pagerBlockMock->expects($this->never())
            ->method('setLimit');
        $pagerBlockMock->expects($this->never())
            ->method('setCollection');
        $pagerBlockMock->expects($this->never())
            ->method('setAvailableLimit');

        //isPagerDisplayed() call
        $this->itemCollectionMock->expects($this->never())
            ->method('getSize');

        $pagerBlockMock->expects($this->never())
            ->method('setShowAmounts');

        $this->block->setLayout($this->layoutMock);
        $this->assertFalse($this->block->isPagerDisplayed());
        $this->assertEmpty($this->block->getPagerHtml());
    }
}
