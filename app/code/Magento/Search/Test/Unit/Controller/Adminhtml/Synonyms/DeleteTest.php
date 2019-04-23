<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Unit\Controller\Adminhtml\Synonyms;

class DeleteTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Search\Controller\Adminhtml\Synonyms\Delete */
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

    /**
     * @var \Magento\Search\Model\SynonymGroup|\PHPUnit_Framework_MockObject_MockObject $synonymGroupMock
     */
    protected $synonymGroupMock;

    /**
     * @var \Magento\Search\Api\Data\SynonymGroupRepositoryInterface $repository
     */
    protected $repository;

    /**
     * @inheritdoc
     */
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
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getParam', 'isPost'])
            ->getMockForAbstractClass();
        $this->requestMock->expects($this->any())->method('isPost')->willReturn(true);
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
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRedirectMock);
        $this->contextMock = $this->getMock(
            \Magento\Backend\App\Action\Context::class,
            [],
            [],
            '',
            false
        );
        $this->synonymGroupMock = $this->getMock(
            \Magento\Search\Model\SynonymGroup::class,
            [],
            [],
            '',
            false
        );
        $this->repository = $this->getMock(
            \Magento\Search\Api\SynonymGroupRepositoryInterface::class,
            [],
            [],
            '',
            false
        );
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);
        $this->deleteController = $this->objectManager->getObject(
            \Magento\Search\Controller\Adminhtml\Synonyms\Delete::class,
            [
                'context' => $this->contextMock,
                'synGroupRepository' => $this->repository,
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
