<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Controller\Adminhtml\Email\Template;

/**
 * @covers \Magento\Email\Controller\Adminhtml\Email\Template\Edit
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Email\Controller\Adminhtml\Email\Template\Edit
     */
    protected $editController;

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
     * @var \Magento\Backend\Block\Widget\Breadcrumbs|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $editBlockMock;

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

    /**
     * @var \Magento\Email\Model\Template|\PHPUnit\Framework\MockObject\MockObject
     */
    private $templateMock;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->getMockBuilder(\Magento\Framework\App\View::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadLayout', 'getLayout', 'getPage', 'renderLayout'])
            ->getMock();
        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBlock', 'createBlock', 'setChild'])
            ->getMock();
        $this->menuBlockMock = $this->getMockBuilder(\Magento\Backend\Block\Menu::class)
            ->disableOriginalConstructor()
            ->setMethods(['setActive', 'getMenuModel', 'getParentItems'])
            ->getMock();
        $this->breadcrumbsBlockMock = $this->getMockBuilder(\Magento\Backend\Block\Widget\Breadcrumbs::class)
            ->disableOriginalConstructor()
            ->setMethods(['addLink'])
            ->getMock();
        $this->editBlockMock = $this->getMockBuilder(\Magento\Backend\Block\Widget\Breadcrumbs::class)
            ->disableOriginalConstructor()
            ->setMethods(['setEditMode'])
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
        $this->templateMock = $this->getMockBuilder(\Magento\Email\Model\BackendTemplate::class)
            ->setMethods(['getId', 'getTemplateCode', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->templateMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->templateMock->expects($this->any())
            ->method('getTemplateCode')
            ->willReturn('My Template');
        $this->templateMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();

        $this->viewMock->expects($this->atLeastOnce())
            ->method('getLayout')
            ->willReturn($this->layoutMock);
        $this->layoutMock->expects($this->any())
            ->method('getBlock')
            ->willReturnMap(
                [
                    ['menu', $this->menuBlockMock],
                    ['breadcrumbs', $this->breadcrumbsBlockMock],
                    ['edit', $this->editBlockMock]
                ]
            );
        $this->menuBlockMock->expects($this->any())
            ->method('getMenuModel')
            ->willReturnSelf();
        $this->menuBlockMock->expects($this->any())
            ->method('getParentItems')
            ->willReturn([]);
        $this->viewMock->expects($this->any())
            ->method('getPage')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);
        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(
                \Magento\Email\Block\Adminhtml\Template\Edit::class,
                'template_edit',
                [
                    'data' => [
                        'email_template' => $this->templateMock
                    ]
                ]
            )->willReturn($this->editBlockMock);
        $this->editBlockMock->expects($this->once())
            ->method('setEditMode')
            ->willReturnSelf();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $objectManagerMock = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Email\Model\BackendTemplate::class)
            ->willReturn($this->templateMock);
        $this->context = $objectManager->getObject(
            \Magento\Backend\App\Action\Context::class,
            [
                'request' => $this->requestMock,
                'objectManager' => $objectManagerMock,
                'view' => $this->viewMock
            ]
        );
        $this->editController = $objectManager->getObject(
            \Magento\Email\Controller\Adminhtml\Email\Template\Edit::class,
            [
                'context' => $this->context,
            ]
        );
    }

    /**
     * @covers \Magento\Email\Controller\Adminhtml\Email\Template\Edit::execute
     */
    public function testExecuteNewTemplate()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('id')
            ->willReturn(0);
        $this->pageTitleMock->expects($this->any())
            ->method('prepend')
            ->willReturnMap(
                [
                    ['Email Templates', $this->returnSelf()],
                    ['New Template', $this->returnSelf()]
                ]
            );
        $this->breadcrumbsBlockMock->expects($this->any())
            ->method('addLink')
            ->willReturnMap(
                [
                    ['Transactional Emails', 'Transactional Emails', null, $this->returnSelf()],
                    ['New Template', 'New System Template', null, $this->returnSelf()]
                ]
            );

        $this->assertNull($this->editController->execute());
    }

    /**
     * @covers \Magento\Email\Controller\Adminhtml\Email\Template\Edit::execute
     */
    public function testExecuteEdit()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('id')
            ->willReturn(1);
        $this->pageTitleMock->expects($this->any())
            ->method('prepend')
            ->willReturnMap(
                [
                    ['Email Templates', $this->returnSelf()],
                    ['My Template', $this->returnSelf()]
                ]
            );
        $this->breadcrumbsBlockMock->expects($this->any())
            ->method('addLink')
            ->willReturnMap(
                [
                    ['Transactional Emails', 'Transactional Emails', null, $this->returnSelf()],
                    ['Edit Template', 'Edit System Template', null, $this->returnSelf()]
                ]
            );

        $this->assertNull($this->editController->execute());
    }
}
