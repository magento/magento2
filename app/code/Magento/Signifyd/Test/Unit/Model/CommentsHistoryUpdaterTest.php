<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\CommentsHistoryUpdater;
use PHPUnit\Framework\MockObject\MockObject as MockObject;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;

/**
 * Contains tests for comments history updater class.
 */
class CommentsHistoryUpdaterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var int
     */
    private static $orderId = 123;

    /**
     * @var string
     */
    private static $message = 'Case is created.';

    /**
     * @var string
     */
    private static $status = 'On Hold';

    /**
     * @var CommentsHistoryUpdater
     */
    private $updater;

    /**
     * @var HistoryFactory|MockObject
     */
    private $historyFactory;

    /**
     * @var CaseInterface|MockObject
     */
    private $caseEntity;

    /**
     * @var OrderStatusHistoryInterface|MockObject
     */
    private $historyEntity;

    /**
     * @var OrderStatusHistoryRepositoryInterface|MockObject
     */
    private $historyRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->historyFactory = $this->getMockBuilder(HistoryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'save'])
            ->getMock();

        $this->historyRepository = $this->getMockBuilder(OrderStatusHistoryRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->caseEntity = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrderId'])
            ->getMockForAbstractClass();

        $this->initCommentMock();

        $this->updater = $objectManager->getObject(CommentsHistoryUpdater::class, [
            'historyFactory' => $this->historyFactory,
            'historyRepository' => $this->historyRepository
        ]);
    }

    /**
     * Checks a test case when updater throws an exception while saving history comment.
     *
     * @covers \Magento\Signifyd\Model\CommentsHistoryUpdater::addComment
     */
    public function testAddCommentWithException()
    {
        $this->expectException(\Exception::class);

        $this->caseEntity->expects(self::once())
            ->method('getOrderId')
            ->willReturn(self::$orderId);

        $this->historyEntity->method('setStatus')
            ->with('')
            ->willReturnSelf();
        $this->historyRepository->expects(self::once())
            ->method('save')
            ->with($this->historyEntity)
            ->willThrowException(new \Exception('Cannot save comment message.'));

        $this->updater->addComment($this->caseEntity, __(self::$message));
    }

    /**
     * Checks a test case when updater successfully saves history comment.
     *
     * @covers \Magento\Signifyd\Model\CommentsHistoryUpdater::addComment
     */
    public function testAddComment()
    {
        $this->caseEntity->expects(self::once())
            ->method('getOrderId')
            ->willReturn(self::$orderId);

        $this->historyEntity->method('setStatus')
            ->with(self::$status)
            ->willReturnSelf();
        $this->historyRepository->expects(self::once())
            ->method('save')
            ->with($this->historyEntity)
            ->willReturnSelf();

        $this->updater->addComment($this->caseEntity, __(self::$message), self::$status);
    }

    /**
     * Checks a test when message does not specified.
     *
     * @covers \Magento\Signifyd\Model\CommentsHistoryUpdater::addComment
     */
    public function testAddCommentWithoutMessage()
    {
        $this->caseEntity->expects(self::never())
            ->method('getOrderId');

        $this->historyFactory->expects(self::never())
            ->method('save');

        $phrase = '';
        $this->updater->addComment($this->caseEntity, __($phrase));
    }

    /**
     * Creates mock object for history entity.
     *
     * @return void
     */
    private function initCommentMock()
    {
        $this->historyEntity = $this->getMockBuilder(OrderStatusHistoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setParentId', 'setComment', 'setEntityName', 'save'])
            ->getMockForAbstractClass();

        $this->historyFactory->method('create')
            ->willReturn($this->historyEntity);

        $this->historyEntity->method('setParentId')
            ->with(self::$orderId)
            ->willReturnSelf();
        $this->historyEntity->method('setComment')
            ->with(self::$message)
            ->willReturnSelf();
        $this->historyEntity->method('setEntityName')
            ->with('order')
            ->willReturnSelf();
    }
}
