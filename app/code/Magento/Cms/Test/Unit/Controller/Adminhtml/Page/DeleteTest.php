<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Page;

class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Cms\Controller\Adminhtml\Page\Delete */
    protected $deleteController;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /** @var \Magento\Backend\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject */
    protected $contextMock;

    /** @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $resultRedirectFactoryMock;

    /** @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject */
    protected $resultRedirectMock;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $messageManagerMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $requestMock;

    /** @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $objectManagerMock;

    /** @var \Magento\Cms\Model\Page|\PHPUnit\Framework\MockObject\MockObject $pageMock */
    protected $pageMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $eventManagerMock;

    /** @var string */
    protected $title = 'This is the title of the page.';

    /** @var int */
    protected $pageId = 1;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);

        $this->requestMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getParam']
        );

        $this->pageMock = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'delete', 'getTitle'])
            ->getMock();

        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\RedirectFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);

        $this->contextMock = $this->createMock(\Magento\Backend\App\Action\Context::class);

        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())->method('getEventManager')->willReturn($this->eventManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->deleteController = $this->objectManager->getObject(
            \Magento\Cms\Controller\Adminhtml\Page\Delete::class,
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
            ->with(\Magento\Cms\Model\Page::class)
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
            ->with(\Magento\Cms\Model\Page::class)
            ->willReturn($this->pageMock);

        $this->pageMock->expects($this->once())
            ->method('load')
            ->with($this->pageId);
        $this->pageMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($this->title);
        $this->pageMock->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception(__($errorMsg)));

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
