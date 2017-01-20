<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\Model;

use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Signifyd\Api\Data\CaseInterface;
use Magento\Signifyd\Model\CommentsHistoryUpdater;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Contains tests for comments history updater class.
 */
class CommentsHistoryUpdaterTest extends \PHPUnit_Framework_TestCase
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
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->historyFactory = $this->getMockBuilder(HistoryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->caseEntity = $this->getMockBuilder(CaseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrderId'])
            ->getMockForAbstractClass();

        $this->initCommentMock();

        $this->updater = $objectManager->getObject(CommentsHistoryUpdater::class, [
            'historyFactory' => $this->historyFactory
        ]);
    }

    /**
     * Checks a test case when updater throws an exception while saving history comment.
     *
     * @covers \Magento\Signifyd\Model\CommentsHistoryUpdater::addComment
     * @expectedException \Exception
     */
    public function testAddCommentWithException()
    {
        $this->caseEntity->expects(self::once())
            ->method('getOrderId')
            ->willReturn(self::$orderId);

        $this->historyEntity->expects(self::once())
            ->method('save')
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

        $this->historyEntity->expects(self::once())
            ->method('save')
            ->willReturnSelf();

        $this->updater->addComment($this->caseEntity, __(self::$message));
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

        $this->historyFactory->expects(self::any())
            ->method('create')
            ->willReturn($this->historyEntity);

        $this->historyEntity->expects(self::any())
            ->method('setParentId')
            ->with(self::$orderId)
            ->willReturnSelf();
        $this->historyEntity->expects(self::any())
            ->method('setComment')
            ->with(self::$message)
            ->willReturnSelf();
        $this->historyEntity->expects(self::any())
            ->method('setEntityName')
            ->with('order')
            ->willReturnSelf();
    }
}
