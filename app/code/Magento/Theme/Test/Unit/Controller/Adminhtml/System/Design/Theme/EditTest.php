<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Theme;

use Magento\Backend\Block\Menu;
use Magento\Backend\Model\Menu\Item;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Tab\Css;
use Magento\Theme\Helper\Theme;
use Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\ThemeTestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends ThemeTestCase
{
    /**
     * @var string
     */
    protected $name = 'Edit';

    /**
     * @return void
     */
    public function testExecuteWithoutLoadedTheme(): void
    {
        $themeId = 23;
        $this->_request
            ->method('getParam')
            ->with('id')
            ->willReturn($themeId);

        $theme = $this->getMockForAbstractClass(
            ThemeInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['setType', 'load', 'getId', 'isVisible']
        );
        $theme->expects($this->once())
            ->method('setType');
        $theme->expects($this->once())
            ->method('load')
            ->with($themeId)
            ->willReturnSelf();
        $theme->expects($this->once())
            ->method('getId')
            ->willReturn($themeId);
        $theme->expects($this->once())
            ->method('isVisible')
            ->willReturn(false);

        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with(ThemeInterface::class)
            ->willReturn($theme);
        $this->messageManager->expects($this->once())
            ->method('addError');
        $this->session->expects($this->once())
            ->method('setIsUrlNotice')
            ->with(true);
        $this->actionFlag->expects($this->once())
            ->method('get')
            ->willReturn(true);
        $this->response->expects($this->once())
            ->method('setRedirect')
            ->with('http://return.url');
        $this->backendHelper->expects($this->once())
            ->method('getUrl')
            ->willReturn('http://return.url');

        $this->_model->execute();
    }

    /**
     * @return void
     */
    public function testExecuteWithException(): void
    {
        $themeId = 23;
        $this->_request
            ->method('getParam')
            ->with('id')
            ->willReturn($themeId);

        $theme = $this->getMockForAbstractClass(
            ThemeInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['setType', 'load', 'getId', 'isVisible']
        );
        $theme->expects($this->once())
            ->method('setType');
        $theme->expects($this->once())
            ->method('load')
            ->with($themeId)
            ->willReturnSelf();
        $theme->expects($this->once())
            ->method('getId')
            ->willReturn($themeId);
        $theme->expects($this->once())
            ->method('isVisible')
            ->willReturn(true);

        $this->_objectManagerMock->expects($this->once())
            ->method('create')
            ->with(ThemeInterface::class)
            ->willReturn($theme);

        $this->coreRegistry
            ->expects($this->once())
            ->method('register')
            ->willThrowException(new \Exception('Message'));

        $logger = $this->getMockForAbstractClass(LoggerInterface::class, [], '', false);
        $logger->expects($this->once())
            ->method('critical');
        $this->_objectManagerMock->expects($this->once())
            ->method('get')
            ->with(LoggerInterface::class)
            ->willReturn($logger);

        $this->messageManager->expects($this->once())
            ->method('addError');
        $this->session->expects($this->once())
            ->method('setIsUrlNotice')
            ->with(true);
        $this->actionFlag->expects($this->once())
            ->method('get')
            ->willReturn(true);
        $this->response->expects($this->once())
            ->method('setRedirect')
            ->with('http://return.url');
        $this->backendHelper->expects($this->once())
            ->method('getUrl')
            ->willReturn('http://return.url');

        $this->_model->execute();
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute(): void
    {
        $themeId = 23;

        $layout = $this->getMockForAbstractClass(LayoutInterface::class, [], '', false);
        $tab = $this->getMockBuilder(Css::class)
            ->addMethods(['setFiles'])
            ->onlyMethods(['canShowTab'])
            ->disableOriginalConstructor()
            ->getMock();
        $menu = $this->getMockBuilder(Menu::class)
            ->addMethods(['setActive'])
            ->onlyMethods(['getMenuModel'])
            ->disableOriginalConstructor()
            ->getMock();
        $menuModel = $this->createMock(\Magento\Backend\Model\Menu::class);
        $themeHelper = $this->createMock(Theme::class);
        $cssAsset = $this->getMockForAbstractClass(LocalInterface::class, [], '', false);
        $menuItem = $this->createMock(Item::class);
        $resultPage = $this->createMock(Page::class);
        $pageConfig = $this->createMock(Config::class);
        $pageTitle = $this->createMock(Title::class);
        $this->_request
            ->method('getParam')
            ->with('id')
            ->willReturn($themeId);

        $theme = $this->getMockForAbstractClass(
            ThemeInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['setType', 'load', 'getId', 'isVisible']
        );
        $theme->expects($this->once())
            ->method('setType');
        $theme->expects($this->once())
            ->method('load')
            ->with($themeId)
            ->willReturnSelf();
        $theme->expects($this->once())
            ->method('getId')
            ->willReturn($themeId);
        $theme->expects($this->once())
            ->method('isVisible')
            ->willReturn(true);

        $this->_objectManagerMock
            ->expects($this->once())
            ->method('create')
            ->with(ThemeInterface::class)
            ->willReturn($theme);

        $this->coreRegistry
            ->expects($this->once())
            ->method('register')
            ->with('current_theme', $theme);
        $this->view->expects($this->once())
            ->method('loadLayout');
        $tab->expects($this->once())
            ->method('canShowTab')
            ->willReturn(true);
        $tab->expects($this->once())
            ->method('setFiles')
            ->with($cssAsset);
        $menu->expects($this->once())
            ->method('setActive')
            ->with('Magento_Theme::system_design_theme');
        $menu->expects($this->once())
            ->method('getMenuModel')
            ->willReturn($menuModel);
        $menuModel->expects($this->once())
            ->method('getParentItems')
            ->with('Magento_Theme::system_design_theme')
            ->willReturn([$menuItem]);
        $menuItem->expects($this->once())
            ->method('getTitle')
            ->willReturn('Title');

        $layout
            ->method('getBlock')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['theme_edit_tabs_tab_css_tab'] => $tab,
                ['menu'] => $menu
            });

        $this->view->expects($this->atLeastOnce())
            ->method('getLayout')
            ->willReturn($layout);

        $themeHelper->expects($this->once())
            ->method('getCssAssets')
            ->with($theme)
            ->willReturn($cssAsset);
        $this->_objectManagerMock->expects($this->once())
            ->method('get')
            ->with(Theme::class)
            ->willReturn($themeHelper);
        $this->view->expects($this->once())
            ->method('getPage')
            ->willReturn($resultPage);
        $resultPage->expects($this->once())
            ->method('getConfig')
            ->willReturn($pageConfig);
        $pageConfig->expects($this->once())
            ->method('getTitle')
            ->willReturn($pageTitle);
        $pageTitle->expects($this->once())
            ->method('prepend')
            ->with('Title');
        $this->view->expects($this->once())
            ->method('renderLayout');

        $this->_model->execute();
    }
}
