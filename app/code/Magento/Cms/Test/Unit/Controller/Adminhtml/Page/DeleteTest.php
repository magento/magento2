<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Page;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Cms\Controller\Adminhtml\Page\Delete;
use Magento\Cms\Model\Page;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\ObjectManager\ObjectManager as FrameworkObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteTest extends TestCase
{
    /** @var Delete */
    protected $deleteController;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var Context|MockObject */
    protected $contextMock;

    /** @var RedirectFactory|MockObject */
    protected $resultRedirectFactoryMock;

    /** @var Redirect|MockObject */
    protected $resultRedirectMock;

    /** @var ManagerInterface|MockObject */
    protected $messageManagerMock;

    /** @var RequestInterface|MockObject */
    protected $requestMock;

    /** @var FrameworkObjectManager|MockObject */
    protected $objectManagerMock;

    /** @var Page|MockObject $pageMock */
    protected $pageMock;

    /** @var EventManagerInterface|MockObject */
    protected $eventManagerMock;

    /** @var string */
    protected $title = 'This is the title of the page.';

    /** @var int */
    protected $pageId = 1;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->messageManagerMock = $this->createMock(ManagerInterface::class);

        $this->requestMock = $this->createMock(
            RequestInterface::class
        );

        $this->pageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'delete', 'getTitle'])
            ->getMock();

        $this->objectManagerMock = $this->getMockBuilder(FrameworkObjectManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->onlyMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            RedirectFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->eventManagerMock = $this->createMock(EventManagerInterface::class);

        $this->contextMock = $this->createMock(Context::class);

        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())->method('getEventManager')->willReturn($this->eventManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->deleteController = $this->objectManager->getObject(
            Delete::class,
            [
                'context' => $this->contextMock,
            ]
        );
    }

    public function testDeleteAction()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn($this->pageId);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Page::class)
            ->willReturn($this->pageMock);

        $this->pageMock->expects($this->once())
            ->method('load')
            ->with($this->pageId);
        $this->pageMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($this->title);
        $this->pageMock->expects($this->once())
            ->method('delete');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('The page has been deleted.'));
        $this->messageManagerMock->expects($this->never())
            ->method('addErrorMessage');

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'adminhtml_cmspage_on_delete',
                ['title' => $this->title, 'status' => 'success']
            );

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->deleteController->execute());
    }

    public function testDeleteActionNoId()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn(null);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('We can\'t find a page to delete.'));
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->deleteController->execute());
    }

    public function testDeleteActionThrowsException()
    {
        $errorMsg = 'Can\'t delete the page';

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn($this->pageId);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Page::class)
            ->willReturn($this->pageMock);

        $this->pageMock->expects($this->once())
            ->method('load')
            ->with($this->pageId);
        $this->pageMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($this->title);
        $this->pageMock->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception($errorMsg));

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'adminhtml_cmspage_on_delete',
                ['title' => $this->title, 'status' => 'fail']
            );

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($errorMsg);
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit', ['page_id' => $this->pageId])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->deleteController->execute());
    }
}
