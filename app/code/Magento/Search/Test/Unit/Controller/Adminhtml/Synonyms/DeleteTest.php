<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Controller\Adminhtml\Synonyms;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Api\SynonymGroupRepositoryInterface;
use Magento\Search\Controller\Adminhtml\Synonyms\Delete;
use Magento\Search\Model\SynonymGroup;
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

    /** @var \Magento\Framework\ObjectManager\ObjectManager|MockObject */
    protected $objectManagerMock;

    /**
     * @var SynonymGroup|MockObject $synonymGroupMock
     */
    protected $synonymGroupMock;

    /**
     * @var \Magento\Search\Api\Data\SynonymGroupRepositoryInterface $repository
     */
    protected $repository;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);

        $this->requestMock = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getParam']
        );

        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            RedirectFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->contextMock = $this->createMock(Context::class);

        $this->synonymGroupMock = $this->createMock(SynonymGroup::class);

        $this->repository = $this->getMockForAbstractClass(SynonymGroupRepositoryInterface::class);

        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);

        $this->deleteController = $this->objectManager->getObject(
            Delete::class,
            [
                'context' => $this->contextMock,
                'synGroupRepository' => $this->repository
            ]
        );
    }

    public function testDeleteAction()
    {
        $this->requestMock->expects($this->once())->method('getParam')->with('group_id')->willReturn(10);

        $this->repository->expects($this->once())->method('delete')->with($this->synonymGroupMock);
        $this->repository->expects($this->once())->method('get')->with(10)->willReturn($this->synonymGroupMock);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('The synonym group has been deleted.'));

        $this->messageManagerMock->expects($this->never())->method('addErrorMessage');

        $this->resultRedirectMock->expects($this->once())->method('setPath')->with('*/*/')->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->deleteController->execute());
    }

    public function testDeleteActionNoId()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn(null);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('We can\'t find a synonym group to delete.'));
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->deleteController->execute());
    }
}
