<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Controller\Adminhtml\Block;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Cms\Controller\Adminhtml\Block\Delete;
use Magento\Cms\Model\Block;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends TestCase
{
    /**
     * @var Delete
     */
    protected $deleteController;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Block|MockObject $blockMock
     */
    protected $blockMock;

    /**
     * @var int
     */
    protected $blockId = 1;

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

        $this->blockMock = $this->getMockBuilder(Block::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'delete'])
            ->getMock();

        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->onlyMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(
            RedirectFactory::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultRedirectFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRedirectMock);

        $this->contextMock = $this->createMock(Context::class);

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
            ]
        );
    }

    public function testDeleteAction()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn($this->blockId);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Block::class)
            ->willReturn($this->blockMock);

        $this->blockMock->expects($this->once())
            ->method('load')
            ->with($this->blockId);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You deleted the block.'));
        $this->messageManagerMock->expects($this->never())
            ->method('addErrorMessage');

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
            ->with(__('We can\'t find a block to delete.'));
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
        $errorMsg = 'Can\'t create the block';

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->willReturn($this->blockId);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Block::class)
            ->willThrowException(new \Exception($errorMsg));

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($errorMsg);
        $this->messageManagerMock->expects($this->never())
            ->method('addSuccessMessage');

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/edit', ['block_id' => $this->blockId])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->deleteController->execute());
    }
}
