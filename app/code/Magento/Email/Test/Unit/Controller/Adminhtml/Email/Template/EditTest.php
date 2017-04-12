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
class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Email\Controller\Adminhtml\Email\Template\Edit
     */
    protected $editController;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Backend\Block\Menu|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $menuBlockMock;

    /**
     * @var \Magento\Backend\Block\Widget\Breadcrumbs|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $breadcrumbsBlockMock;

    /**
     * @var \Magento\Backend\Block\Widget\Breadcrumbs|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $editBlockMock;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageTitleMock;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['registry', 'register'])
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
            ->will($this->returnSelf());
        $this->menuBlockMock->expects($this->any())
            ->method('getParentItems')
            ->will($this->returnValue([]));
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
            ->with(\Magento\Email\Block\Adminhtml\Template\Edit::class, 'template_edit', [])
            ->willReturn($this->editBlockMock);
        $this->editBlockMock->expects($this->once())
            ->method('setEditMode')
            ->willReturnSelf();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $templateMock = $this->getMockBuilder(\Magento\Email\Model\Template::class)
            ->disableOriginalConstructor()
            ->getMock();
        $templateMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $templateMock->expects($this->any())
            ->method('getTemplateCode')
            ->willReturn('My Template');
        $objectManagerMock = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with(\Magento\Email\Model\BackendTemplate::class)
            ->willReturn($templateMock);
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
                'coreRegistry' => $this->registryMock
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
        $this->registryMock->expects($this->atLeastOnce())
            ->method('registry')
            ->willReturnMap(
                [
                    ['email_template', true],
                    ['current_email_template', true]
                ]
            );
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
        $this->registryMock->expects($this->atLeastOnce())
            ->method('registry')
            ->willReturnMap(
                [
                    ['email_template', false],
                    ['current_email_template', false]
                ]
            );
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
