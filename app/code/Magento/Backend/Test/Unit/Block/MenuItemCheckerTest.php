<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block;

use Magento\Backend\Model\Menu\Item;
use Magento\Backend\Model\Menu;
use Magento\Backend\Block\MenuItemChecker;

class MenuItemCheckerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private $activeMenuItemMock;

    /**
     * @var Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private $menuItemMock;

    /**
     * @var Menu|\PHPUnit_Framework_MockObject_MockObject
     */
    private $menuMock;

    /**
     * @var MenuItemChecker;
     */
    private $menuItemChecker;

    protected function setUp()
    {
        $this->menuItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->activeMenuItemMock = $this->getMockBuilder(Item::class)
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
    public function testIsItemActive($activeItemId, $itemId, $isItem, $expected)
    {
        $this->menuMock = $this->getMockBuilder(Menu::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->menuItemMock->expects($this->any())->method('getId')->willReturn($itemId);
        $this->activeMenuItemMock->expects($this->any())->method('getId')->willReturn($activeItemId);
        $this->menuItemMock->expects($this->any())->method('getChildren')->willReturn($this->menuMock);
        $this->menuMock->expects($this->any())
            ->method('get')
            ->with($activeItemId)
            ->willReturn($isItem ? $this->activeMenuItemMock : null);
        $this->assertEquals(
            $expected,
            $this->menuItemChecker->isItemActive($this->activeMenuItemMock, $this->menuItemMock, 0)
        );
    }

    public function testIsItemActiveLevelNotZero()
    {
        $this->assertFalse(
            $this->menuItemChecker->isItemActive($this->activeMenuItemMock, $this->menuItemMock, 1)
        );
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'outputItemEquals' => ['1', '1', false, true],
            'outputItemIsChild' => ['1', '2', true, true],
            'outputItemIsChildNull' => ['1', '2', false, false],
        ];
    }
}
