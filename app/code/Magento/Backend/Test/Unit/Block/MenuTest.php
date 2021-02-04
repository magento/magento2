<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block;

use Magento\Backend\Model\Menu\Item;
use Magento\Backend\Model\Menu as MenuModel;
use Magento\Backend\Block\Menu;
use Magento\Backend\Model\UrlInterface;
use Magento\Backend\Model\Menu\Filter\IteratorFactory;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Menu\Config;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Backend\Block\MenuItemChecker;
use Magento\Backend\Block\AnchorRenderer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class MenuTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Item|\PHPUnit\Framework\MockObject\MockObject
     */
    private $activeItemMock;

    /**
     * @var MenuModel|\PHPUnit\Framework\MockObject\MockObject
     */
    private $menuModelMock;

    /**
     * @var UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $urlMock;

    /**
     * @var IteratorFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $iteratorFactoryMock;

    /**
     * @var Session|\PHPUnit\Framework\MockObject\MockObject
     */
    private $authSessionMock;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $menuConfigMock;

    /**
     * @var ResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $localeResolverMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var MenuItemChecker|\PHPUnit\Framework\MockObject\MockObject
     */
    private $menuItemCheckerMock;

    /**
     * @var AnchorRenderer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $anchorRendererMock;

    /**
     * @var Menu
     */
    private $menu;

    protected function setUp(): void
    {
        $this->activeItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->iteratorFactoryMock = $this->getMockBuilder(IteratorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->menuConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeResolverMock = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->anchorRendererMock = $this->getMockBuilder(AnchorRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->menu =  $this->objectManagerHelper->getObject(
            Menu::class,
            [
                'url' => $this->urlMock,
                'iteratorFactory' => $this->iteratorFactoryMock,
                'authSession' => $this->authSessionMock,
                'menuConfig' => $this->menuConfigMock,
                'localeResolver' => $this->localeResolverMock,
                'menuItemChecker' => $this->menuItemCheckerMock,
                'anchorRenderer' => $this->anchorRendererMock
            ]
        );
    }

    public function testGetActiveItemModelMenuIsNotNull()
    {
        $this->menuModelMock = $this->getMockBuilder(MenuModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->menu->setActive($this->activeItemMock);
        $this->menuConfigMock->expects($this->once())->method('getMenu')->willReturn($this->menuModelMock);
        $this->menuModelMock->expects($this->once())
            ->method('get')
            ->willReturn($this->activeItemMock);

        $this->assertEquals($this->activeItemMock, $this->menu->getActiveItemModel());
    }

    public function testGetActiveItemModelMenuIsNull()
    {
        $this->menuModelMock = $this->getMockBuilder(MenuModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->menu->setActive(null);
        $this->menuConfigMock->expects($this->once())->method('getMenu')->willReturn($this->menuModelMock);
        $this->menuModelMock->expects($this->once())
            ->method('get')
            ->willReturn(null);

        $this->assertFalse($this->menu->getActiveItemModel());
    }
}
