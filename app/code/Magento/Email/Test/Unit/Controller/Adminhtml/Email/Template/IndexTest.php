<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Controller\Adminhtml\Email\Template;

/**
 * @covers \Magento\Email\Controller\Adminhtml\Email\Template\Index
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Email\Controller\Adminhtml\Email\Template\Index
     */
    protected $indexController;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\View|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Backend\Block\Menu|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $menuBlockMock;

    /**
     * @var \Magento\Backend\Block\Widget\Breadcrumbs|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $breadcrumbsBlockMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $pageTitleMock;

    protected function setUp(): void
    {
        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->getMockBuilder(\Magento\Framework\App\View::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadLayout', 'getLayout', 'getPage', 'renderLayout'])
            ->getMock();
        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBlock'])
            ->getMock();
        $this->menuBlockMock = $this->getMockBuilder(\Magento\Backend\Block\Menu::class)
            ->disableOriginalConstructor()
            ->setMethods(['setActive', 'getMenuModel', 'getParentItems'])
            ->getMock();
        $this->breadcrumbsBlockMock = $this->getMockBuilder(\Magento\Backend\Block\Widget\Breadcrumbs::class)
            ->disableOriginalConstructor()
            ->setMethods(['addLink'])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['setActiveMenu', 'getConfig', 'addBreadcrumb'])
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $objectManager->getObject(
            \Magento\Backend\App\Action\Context::class,
            [
                'request' => $this->requestMock,
                'view' => $this->viewMock
            ]
        );
        $this->indexController = $objectManager->getObject(
            \Magento\Email\Controller\Adminhtml\Email\Template\Index::class,
            [
                'context' => $this->context,
            ]
        );
    }

    /**
     * @covers \Magento\Email\Controller\Adminhtml\Email\Template\Index::execute
     */
    public function testExecute()
    {
        $this->prepareExecute();

        $this->viewMock->expects($this->atLeastOnce())
            ->method('getLayout')
            ->willReturn($this->layoutMock);
        $this->layoutMock->expects($this->at(0))
            ->method('getBlock')
            ->with('menu')
            ->willReturn($this->menuBlockMock);
        $this->menuBlockMock->expects($this->any())
            ->method('getMenuModel')
            ->willReturnSelf();
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
        $this->layoutMock->expects($this->at(1))
            ->method('getBlock')
            ->with('breadcrumbs')
            ->willReturn($this->breadcrumbsBlockMock);
        $this->breadcrumbsBlockMock->expects($this->any())
            ->method('addLink')
            ->willReturnSelf();

        $this->assertNull($this->indexController->execute());
    }

    /**
     * @covers \Magento\Email\Controller\Adminhtml\Email\Template\Index::execute
     */
    public function testExecuteAjax()
    {
        $this->prepareExecute(true);
        $indexController = $this->getMockBuilder(\Magento\Email\Controller\Adminhtml\Email\Template\Index::class)
            ->setMethods(['getRequest', '_forward'])
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
     */
    protected function prepareExecute($ajax = false)
    {
        $this->requestMock->expects($this->once())
            ->method('getQuery')
            ->with('ajax')
            ->willReturn($ajax);
    }
}
