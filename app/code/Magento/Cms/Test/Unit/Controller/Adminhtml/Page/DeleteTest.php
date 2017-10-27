<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Page;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class DeleteTest
 * @package Magento\Cms\Test\Unit\Controller\Adminhtml\Page
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Controller\Adminhtml\Page\Delete
     */
    protected $deleteController;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Cms\Model\Page|\PHPUnit_Framework_MockObject_MockObject $pageMock
     */
    protected $pageMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Cms\Api\PageRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageRepository;

    /** @var string */
    protected $title = 'This is the title of the page.';

    /** @var int */
    protected $pageId = 1;

    protected function setUp()
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
            ->setMethods(['load', 'delete', 'getTitle', 'getId'])
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

        $this->pageRepository = $this->getMockBuilder(\Magento\Cms\Api\PageRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->deleteController = $this->objectManager->getObject(
            \Magento\Cms\Controller\Adminhtml\Page\Delete::class,
            [
                'context' => $this->contextMock,
                'pageRepository' => $this->pageRepository,
            ]
        );
    }

    public function testDeleteAction()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn($this->pageId);

        $this->objectManagerMock->expects($this->never())
            ->method('create')
            ->with(\Magento\Cms\Model\Page::class);

        $this->pageRepository->expects($this->once())
            ->method('getById')
            ->with($this->pageId)
            ->willReturn($this->pageMock);
        $this->pageRepository->expects($this->once())
            ->method('delete')
            ->with($this->pageMock)
            ->willReturn(true);

        $this->pageMock->expects($this->never())
            ->method('load')
            ->with($this->pageId);
        $this->pageMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($this->title);
        $this->pageMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->pageId);
        $this->pageMock->expects($this->never())
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

        $this->pageRepository->expects($this->never())
            ->method('delete');
        $this->pageRepository->expects($this->never())
            ->method('getById');

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('This page no longer exists.'));
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->deleteController->execute());
    }

    public function testDeleteActionPageNoExists()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn($this->pageId);

        $this->pageRepository->expects($this->never())
            ->method('delete');
        $this->pageRepository->expects($this->once())
            ->method('getById')
            ->willThrowException(
                new NoSuchEntityException(__('No such entity.'))
            );

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('This page no longer exists.'));
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->deleteController->execute());
    }

    /**
     * @param $exception
     * @param $errorMsg
     * @dataProvider deleteActionThrowsExceptionDataProvider
     */
    public function testDeleteActionThrowsException($exception, $errorMsg)
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn($this->pageId);

        $this->pageRepository->expects($this->once())
            ->method('getById')
            ->willReturn($this->pageMock);
        $this->pageRepository->expects($this->once())
            ->method('delete')
            ->willThrowException($exception);

        $this->objectManagerMock->expects($this->never())
            ->method('create')
            ->with(\Magento\Cms\Model\Page::class);

        $this->pageMock->expects($this->never())
            ->method('load');
        $this->pageMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->pageId);
        $this->pageMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($this->title);
        $this->pageMock->expects($this->never())
            ->method('delete');

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'adminhtml_cmspage_on_delete',
                ['title' => $this->title, 'status' => 'fail']
            );

        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, $errorMsg);
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit', ['page_id' => $this->pageId])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->deleteController->execute());
    }

    /**
     * @return array
     */
    public function deleteActionThrowsExceptionDataProvider()
    {
        return [
            'Exception' => [
                new \Exception(__('Something went wrong while deleting the page.')),
                __('Something went wrong while deleting the page.'),
            ],
            'CouldNotDeleteException' => [
                new LocalizedException(__('Could not delete.')),
                null
            ],
        ];
    }
}
