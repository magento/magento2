<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Block;

use Magento\Backend\Model\Menu\Item;
use Magento\Backend\Model\Menu as MenuModel;
use Magento\Backend\Block\Menu;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Backend\Model\Menu\Filter\IteratorFactory;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Menu\Config;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Backend\Block\MenuItemChecker;
use Magento\Backend\Block\AnchorRenderer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class MenuTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private $activeItemMock;

    /**
     * @var MenuModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $menuModelMock;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    /**
     * @var IteratorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $iteratorFactoryMock;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authSessionMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $menuConfigMock;

    /**
     * @var ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeResolverMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var MenuItemChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $menuItemCheckerMock;

    /**
     * @var AnchorRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $anchorRendererMock;

    /**
     * @var Menu
     */
    private $menu;

    protected function setUp()
    {
        $this->activeItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
            ->getMock();
        $this->menuItemChecker = $this->getMockBuilder(MenuItemChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
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

        $this->assertNull($this->menu->getActiveItemModel());
    }
}
