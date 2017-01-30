<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block;

use Magento\Backend\Model\Menu\Item;
use Magento\Backend\Model\Menu;
use Magento\Backend\Block\MenuItemChecker;

class MenuItemCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private $activeMenuItem;

    /**
     * @var Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private $menuItem;

    /**
     * @var Menu|\PHPUnit_Framework_MockObject_MockObject
     */
    private $menu;

    /**
     * @var MenuItemChecker;
     */
    private $menuItemChecker;

    protected function setUp()
    {
        $this->menuItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->activeMenuItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->menuItemChecker = new MenuItemChecker();
    }

    /**
     * @param int $activeItemId
     * @param int $itemId
     * @param bool $isItem
     * @param bool $expected
     * @dataProvider dataProvider
     */
    public function testIsItemActive( $activeItemId, $itemId, $isItem, $expected)
    {
        $this->menu = $this->getMockBuilder(Menu::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->menuItem->expects($this->any())->method('getId')->willReturn($itemId);
        $this->activeMenuItem->expects($this->any())->method('getId')->willReturn($activeItemId);
        $this->menuItem->expects($this->any())->method('getChildren')->willReturn($this->menu);
        $this->menu->expects($this->any())
            ->method('get')
            ->with($activeItemId)
            ->willReturn($isItem ? $this->activeMenuItem : null);
        $this->assertEquals($expected,
            $this->menuItemChecker->isItemActive($this->activeMenuItem, $this->menuItem, 0)

        );
    }

    public function testIsItemActiveLevelNotZero()
    {
        $this->assertFalse(
            $this->menuItemChecker->isItemActive($this->activeMenuItem, $this->menuItem, 1)

        );
    }

    public function dataProvider()
    {
        return [
            'outputItemEquals' => ['1', '1', false, true],
            'outputItemIsChild' => ['1', '2', true, true],
            'outputItemIsChildNull' => ['1', '2', false, false],
        ];
    }
}
