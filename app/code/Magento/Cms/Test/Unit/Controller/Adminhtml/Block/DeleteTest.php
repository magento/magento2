<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class DeleteTest
 * @package Magento\Cms\Test\Unit\Controller\Adminhtml\Block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Controller\Adminhtml\Block\Delete
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
     * @var \Magento\Cms\Model\Block|\PHPUnit_Framework_MockObject_MockObject $blockMock
     */
    protected $blockMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $blockRepository;

    /** @var string */
    protected $title = 'This is the title of the block.';

    /** @var int */
    protected $blockId = 1;

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

        $this->blockMock = $this->getMockBuilder(\Magento\Cms\Model\Block::class)
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

        $this->blockRepository = $this->getMockBuilder(\Magento\Cms\Api\BlockRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->deleteController = $this->objectManager->getObject(
            \Magento\Cms\Controller\Adminhtml\Block\Delete::class,
            [
                'context' => $this->contextMock,
                'blockRepository' => $this->blockRepository,
            ]
        );
    }

    public function testDeleteAction()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn($this->blockId);

        $this->objectManagerMock->expects($this->never())
            ->method('create')
            ->with(\Magento\Cms\Model\Block::class);

        $this->blockRepository->expects($this->once())
            ->method('getById')
            ->with($this->blockId)
            ->willReturn($this->blockMock);
        $this->blockRepository->expects($this->once())
            ->method('delete')
            ->with($this->blockMock)
            ->willReturn(true);

        $this->blockMock->expects($this->never())
            ->method('load')
            ->with($this->blockId);
        $this->blockMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($this->title);
        $this->blockMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->blockId);
        $this->blockMock->expects($this->never())
            ->method('delete');

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('The block has been deleted.'));
        $this->messageManagerMock->expects($this->never())
            ->method('addErrorMessage');

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'adminhtml_cmsblock_on_delete',
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

        $this->blockRepository->expects($this->never())
            ->method('delete');
        $this->blockRepository->expects($this->never())
            ->method('getById');

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('This block no longer exists.'));
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->deleteController->execute());
    }

    public function testDeleteActionBlockNoExists()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn($this->blockId);

        $this->blockRepository->expects($this->never())
            ->method('delete');
        $this->blockRepository->expects($this->once())
            ->method('getById')
            ->willThrowException(
                new NoSuchEntityException(__('No such entity.'))
            );

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('This block no longer exists.'));
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
            ->willReturn($this->blockId);

        $this->blockRepository->expects($this->once())
            ->method('getById')
            ->willReturn($this->blockMock);
        $this->blockRepository->expects($this->once())
            ->method('delete')
            ->willThrowException($exception);

        $this->objectManagerMock->expects($this->never())
            ->method('create')
            ->with(\Magento\Cms\Model\Block::class);

        $this->blockMock->expects($this->never())
            ->method('load');
        $this->blockMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->blockId);
        $this->blockMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($this->title);
        $this->blockMock->expects($this->never())
            ->method('delete');

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'adminhtml_cmsblock_on_delete',
                ['title' => $this->title, 'status' => 'fail']
            );

        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, $errorMsg);
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit', ['block_id' => $this->blockId])
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
                new \Exception(__('Something went wrong while deleting the block.')),
                __('Something went wrong while deleting the block.'),
            ],
            'CouldNotDeleteException' => [
                new LocalizedException(__('Could not delete.')),
                null
            ],
        ];
    }
}
