<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Controller\Adminhtml\Email\Template;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Block\Menu;
use Magento\Backend\Block\Widget\Breadcrumbs;
use Magento\Email\Controller\Adminhtml\Email\Template\Index;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\View;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Email\Controller\Adminhtml\Email\Template\Index
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    private $indexController;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var \Magento\Framework\App\Request|MockObject
     */
    private $requestMock;

    /**
     * @var View|MockObject
     */
    private $viewMock;

    /**
     * @var Layout|MockObject
     */
    private $layoutMock;

    /**
     * @var Menu|MockObject
     */
    private $menuBlockMock;

    /**
     * @var Breadcrumbs|MockObject
     */
    private $breadcrumbsBlockMock;

    /**
     * @var Page|MockObject
     */
    private $resultPageMock;

    /**
     * @var Config|MockObject
     */
    private $pageConfigMock;

    /**
     * @var Title|MockObject
     */
    private $pageTitleMock;

    /**
     * @var MockObject|Registry
     */
    private $registryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->getMockBuilder(View::class)->disableOriginalConstructor()
            ->onlyMethods(['loadLayout', 'getLayout', 'getPage', 'renderLayout'])
            ->getMock();
        $this->layoutMock = $this->getMockBuilder(Layout::class)->disableOriginalConstructor()
            ->onlyMethods(['getBlock'])
            ->getMock();
        $this->menuBlockMock = $this->getMockBuilder(Menu::class)->disableOriginalConstructor()
            ->onlyMethods(['getMenuModel'])
            ->addMethods(['setActive', 'getParentItems'])
            ->getMock();
        $this->breadcrumbsBlockMock = $this->getMockBuilder(Breadcrumbs::class)->disableOriginalConstructor()
            ->onlyMethods(['addLink'])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(Page::class)->disableOriginalConstructor()
            ->onlyMethods(['getConfig'])
            ->addMethods(['setActiveMenu', 'addBreadcrumb'])
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'view' => $this->viewMock
            ]
        );
        $this->indexController = $objectManager->getObject(
            Index::class,
            [
                'context' => $this->context,
            ]
        );
    }

    /**
     * @covers \Magento\Email\Controller\Adminhtml\Email\Template\Index::execute
     *
     * @return void
     */
    public function testExecute(): void
    {
        $this->prepareExecute();

        $this->viewMock->expects($this->atLeastOnce())
            ->method('getLayout')
            ->willReturn($this->layoutMock);
        $this->menuBlockMock->expects($this->any())
            ->method('getMenuModel')->willReturnSelf();
        $this->menuBlockMock->expects($this->any())
            ->method('getParentItems')
            ->willReturn([]);
        $this->viewMock->expects($this->once())
            ->method('getPage')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);
        $this->pageTitleMock->expects($this->once())
            ->method('prepend')
            ->with('Email Templates');
        $this->layoutMock
            ->method('getBlock')
            ->withConsecutive(['menu'], ['breadcrumbs'])
            ->willReturnOnConsecutiveCalls($this->menuBlockMock, $this->breadcrumbsBlockMock);
        $this->breadcrumbsBlockMock->expects($this->any())
            ->method('addLink')
            ->willReturnSelf();

        $this->assertNull($this->indexController->execute());
    }

    /**
     * @covers \Magento\Email\Controller\Adminhtml\Email\Template\Index::execute
     *
     * @return void
     */
    public function testExecuteAjax(): void
    {
        $this->prepareExecute(true);
        $indexController = $this->getMockBuilder(Index::class)
            ->onlyMethods(['getRequest', '_forward'])
            ->disableOriginalConstructor()
            ->getMock();
        $indexController->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $indexController->expects($this->once())
            ->method('_forward')
            ->with('grid');
        $this->assertNull($indexController->execute());
    }

    /**
     * @param bool $ajax
     *
     * @return void
     */
    protected function prepareExecute(bool $ajax = false): void
    {
        $this->requestMock->expects($this->once())
            ->method('getQuery')
            ->with('ajax')
            ->willReturn($ajax);
    }
}
