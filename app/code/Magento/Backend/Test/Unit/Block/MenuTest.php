<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block;

use Magento\Backend\Block\AnchorRenderer;
use Magento\Backend\Block\Menu;
use Magento\Backend\Block\MenuItemChecker;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Menu as MenuModel;
use Magento\Backend\Model\Menu\Config;
use Magento\Backend\Model\Menu\Filter\IteratorFactory;
use Magento\Backend\Model\Menu\Item;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MenuTest extends TestCase
{
    /**
     * @var Item|MockObject
     */
    private $activeItemMock;

    /**
     * @var MenuModel|MockObject
     */
    private $menuModelMock;

    /**
     * @var Config|MockObject
     */
    private $menuConfigMock;

    /**
     * @var MenuItemChecker|MockObject
     */
    private $menuItemCheckerMock;

    /**
     * @var Menu
     */
    private $menu;

    protected function setUp(): void
    {
        $this->activeItemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $iteratorFactoryMock = $this->getMockBuilder(IteratorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $authSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->menuConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $localeResolverMock = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $anchorRendererMock = $this->getMockBuilder(AnchorRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $objectManagerHelper->prepareObjectManager();

        $this->menu =  $objectManagerHelper->getObject(
            Menu::class,
            [
                'url' => $urlMock,
                'iteratorFactory' => $iteratorFactoryMock,
                'authSession' => $authSessionMock,
                'menuConfig' => $this->menuConfigMock,
                'localeResolver' => $localeResolverMock,
                'menuItemChecker' => $this->menuItemCheckerMock,
                'anchorRenderer' => $anchorRendererMock
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
