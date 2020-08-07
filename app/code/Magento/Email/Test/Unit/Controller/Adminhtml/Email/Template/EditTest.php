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
use Magento\Email\Controller\Adminhtml\Email\Template\Edit;
use Magento\Email\Model\BackendTemplate;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\View;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Email\Controller\Adminhtml\Email\Template\Edit
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends TestCase
{
    /**
     * @var Edit
     */
    protected $editController;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request|MockObject
     */
    protected $requestMock;

    /**
     * @var View|MockObject
     */
    protected $viewMock;

    /**
     * @var Layout|MockObject
     */
    protected $layoutMock;

    /**
     * @var Menu|MockObject
     */
    protected $menuBlockMock;

    /**
     * @var Breadcrumbs|MockObject
     */
    protected $breadcrumbsBlockMock;

    /**
     * @var Breadcrumbs|MockObject
     */
    protected $editBlockMock;

    /**
     * @var Page|MockObject
     */
    protected $resultPageMock;

    /**
     * @var Config|MockObject
     */
    protected $pageConfigMock;

    /**
     * @var Title|MockObject
     */
    protected $pageTitleMock;

    /**
     * @var \Magento\Email\Model\Template|MockObject
     */
    private $templateMock;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadLayout', 'getLayout', 'getPage', 'renderLayout'])
            ->getMock();
        $this->layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBlock', 'createBlock', 'setChild'])
            ->getMock();
        $this->menuBlockMock = $this->getMockBuilder(Menu::class)
            ->disableOriginalConstructor()
            ->setMethods(['setActive', 'getMenuModel', 'getParentItems'])
            ->getMock();
        $this->breadcrumbsBlockMock = $this->getMockBuilder(Breadcrumbs::class)
            ->disableOriginalConstructor()
            ->setMethods(['addLink'])
            ->getMock();
        $this->editBlockMock = $this->getMockBuilder(Breadcrumbs::class)
            ->disableOriginalConstructor()
            ->setMethods(['setEditMode'])
            ->getMock();
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['setActiveMenu', 'getConfig', 'addBreadcrumb'])
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageTitleMock = $this->getMockBuilder(Title::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->templateMock = $this->getMockBuilder(BackendTemplate::class)
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
            ->method('getMenuModel')->willReturnSelf();
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

        $objectManager = new ObjectManager($this);
        $objectManagerMock = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with(BackendTemplate::class)
            ->willReturn($this->templateMock);
        $this->context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'objectManager' => $objectManagerMock,
                'view' => $this->viewMock
            ]
        );
        $this->editController = $objectManager->getObject(
            Edit::class,
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
