<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Page;

class DeleteTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Cms\Controller\Adminhtml\Page\Delete */
    protected $deleteController;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /** @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultRedirectFactoryMock;

    /** @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultRedirectMock;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManagerMock;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /** @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectManagerMock;

    /** @var \Magento\Cms\Model\Page|\PHPUnit_Framework_MockObject_MockObject $pageMock */
    protected $pageMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManagerMock;

    /** @var string */
    protected $title = 'This is the title of the page.';

    /** @var int */
    protected $pageId = 1;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->messageManagerMock = $this->getMock(
            \Magento\Framework\Message\ManagerInterface::class,
            [],
            [],
            '',
            false
        );

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

        $this->eventManagerMock = $this->getMock(\Magento\Framework\Event\ManagerInterface::class, [], [], '', false);

        $this->contextMock = $this->getMock(
            \Magento\Backend\App\Action\Context::class,
            [],
            [],
            '',
            false
        );

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
            ->method('addSuccess')
            ->with(__('The page has been deleted.'));
        $this->messageManagerMock->expects($this->never())
            ->method('addError');

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
            ->method('addError')
            ->with(__('We can\'t find a page to delete.'));
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccess');

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
            ->method('addError')
            ->with($errorMsg);
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccess');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit', ['page_id' => $this->pageId])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->deleteController->execute());
    }
}
