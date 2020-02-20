<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block;

use Magento\Backend\Block\AnchorRenderer;
use Magento\Backend\Block\MenuItemChecker;
use Magento\Backend\Model\Menu\Item;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class AnchorRendererTest extends \PHPUnit\Framework\TestCase
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
     * @var Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaperMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var MenuItemChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $menuItemCheckerMock;

    /**
     * @var AnchorRenderer
     */
    private $anchorRenderer;

    protected function setUp()
    {
        $this->activeMenuItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->menuItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->menuItemWithoutChildrenMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->menuItemCheckerMock = $this->getMockBuilder(MenuItemChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->anchorRenderer =  $this->objectManagerHelper->getObject(
            AnchorRenderer::class,
            [
                'menuItemChecker' => $this->menuItemCheckerMock,
                'escaper' => $this->escaperMock
            ]
        );
    }

    public function testRenderAnchorLevelIsOne()
    {
        $title = 'Title';
        $html =  'Test html';
        $this->menuItemMock->expects($this->once())->method('getUrl')->willReturn('#');
        $this->menuItemMock->expects($this->once())->method('getTitle')->willReturn($title);
        $this->menuItemMock->expects($this->once())->method('hasChildren')->willReturn(true);
        $this->escaperMock->expects($this->once())->method('escapeHtml')->with(__($title))->willReturn($html);

        $expected =  '<strong class="submenu-group-title" role="presentation">'
            . '<span>' . $html . '</span>'
            . '</strong>';

        $this->assertEquals(
            $expected,
            $this->anchorRenderer->renderAnchor($this->activeMenuItemMock, $this->menuItemMock, 1)
        );
    }

    public function testRenderAnchorWithoutChildrenAndLevelIsOne()
    {
        $this->menuItemWithoutChildrenMock->expects($this->once())->method('getUrl')->willReturn('#');
        $this->menuItemWithoutChildrenMock->expects($this->once())->method('hasChildren')->willReturn(false);

        $expected =  '';

        $this->assertEquals(
            $expected,
            $this->anchorRenderer->renderAnchor($this->activeMenuItemMock, $this->menuItemWithoutChildrenMock, 1)
        );
    }

    /**
     * @param bool $hasTarget
     * @dataProvider targetDataProvider
     */
    public function testRenderAnchorLevelIsNotOne($hasTarget)
    {
        $level = 0;
        $title = 'Title';
        $html =  'Test html';
        $url = 'test/url';
        $tooltip = 'Anchor title';
        $onclick = '';
        $target = '_blank';
        $finalTarget = $hasTarget ? ('target=' . $target) : '';
        $this->menuItemMock->expects($this->any())->method('getTarget')->willReturn($hasTarget ? $target : null);
        $this->menuItemMock->expects($this->once())->method('getUrl')->willReturn($url);
        $this->menuItemMock->expects($this->once())->method('getTitle')->willReturn($title);
        $this->escaperMock->expects($this->once())->method('escapeHtml')->with(__($title))->willReturn($html);
        $this->menuItemMock->expects($this->once())->method('hasTooltip')->willReturn(true);
        $this->menuItemMock->expects($this->any())->method('getTooltip')->willReturn(__($tooltip));
        $this->menuItemMock->expects($this->once())->method('hasClickCallback')->willReturn(true);
        $this->menuItemMock->expects($this->once())->method('getClickCallback')->willReturn($onclick);
        $this->menuItemCheckerMock->expects($this->once())
            ->method('isItemActive')
            ->with($this->activeMenuItemMock, $this->menuItemMock, $level)->willReturn(true);

        $expected = '<a href="' . $url . '" ' . $finalTarget . ' ' . 'title="' . $tooltip . '"'
            . ' onclick="' . $onclick . '"'
            . ' class="' . '_active'
            . '">' . '<span>' . $html
            . '</span>' . '</a>';

        $this->assertEquals(
            $expected,
            $this->anchorRenderer->renderAnchor($this->activeMenuItemMock, $this->menuItemMock, $level)
        );
    }

    /**
     * @return array
     */
    public function targetDataProvider()
    {
        return [
            'item has target' => [true],
            'item does not have target' => [false]
        ];
    }
}
