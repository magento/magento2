<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Creditmemo;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoCommentInterfaceFactory;
use Magento\Sales\Api\Data\CreditmemoCommentSearchResultInterfaceFactory;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Comment;
use Magento\Sales\Model\Order\Creditmemo\CommentRepository;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoCommentSender;
use Magento\Sales\Model\Spi\CreditmemoCommentResourceInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CommentRepositoryTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommentRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|CreditmemoCommentResourceInterface
     */
    private $commentResource;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|CreditmemoCommentInterfaceFactory
     */
    private $commentFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|CreditmemoCommentSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CommentRepository
     */
    private $commentRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|CreditmemoCommentSender
     */
    private $creditmemoCommentSender;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|CreditmemoRepositoryInterface
     */
    private $creditmemoRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Creditmemo
     */
    private $creditmemoMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Comment
     */
    private $commentMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|LoggerInterface
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->commentResource = $this->getMockBuilder(CreditmemoCommentResourceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->commentFactory = $this->getMockBuilder(CreditmemoCommentInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultFactory = $this->getMockBuilder(CreditmemoCommentSearchResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->creditmemoRepositoryMock = $this->getMockBuilder(CreditmemoRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->creditmemoCommentSender = $this->getMockBuilder(CreditmemoCommentSender::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMockForAbstractClass();

        $this->creditmemoMock = $this->getMockBuilder(Creditmemo::class)->disableOriginalConstructor()->getMock();
        $this->commentMock = $this->getMockBuilder(Comment::class)->disableOriginalConstructor()->getMock();

        $this->commentRepository = new CommentRepository(
            $this->commentResource,
            $this->commentFactory,
            $this->searchResultFactory,
            $this->collectionProcessor,
            $this->creditmemoCommentSender,
            $this->creditmemoRepositoryMock,
            $this->loggerMock
        );
    }

    public function testSave()
    {
        $comment = "Comment text";
        $creditmemoId = 123;
        $this->commentResource->expects($this->once())
            ->method('save')
            ->with($this->commentMock)
            ->willReturnSelf();
        $this->commentMock->expects($this->once())
            ->method('getIsCustomerNotified')
            ->willReturn(1);
        $this->commentMock->expects($this->once())
            ->method('getParentId')
            ->willReturn($creditmemoId);
        $this->commentMock->expects($this->once())
            ->method('getComment')
            ->willReturn($comment);

        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('get')
            ->with($creditmemoId)
            ->willReturn($this->creditmemoMock);
        $this->creditmemoCommentSender->expects($this->once())
            ->method('send')
            ->with($this->creditmemoMock, true, $comment)
            ->willReturn(true);
        $this->commentRepository->save($this->commentMock);
    }

    /**
     */
    public function testSaveWithException()
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->expectExceptionMessage('Could not save the creditmemo comment.');

        $this->commentResource->expects($this->once())
            ->method('save')
            ->with($this->commentMock)
            ->willThrowException(
                new \Magento\Framework\Exception\CouldNotSaveException(__('Could not save the creditmemo comment.'))
            );

         $this->commentRepository->save($this->commentMock);
    }

    public function testSaveSendCatchException()
    {
        $comment = "Comment text";
        $creditmemoId = 123;
        $this->commentResource->expects($this->once())
            ->method('save')
            ->with($this->commentMock)
            ->willReturnSelf();
        $this->commentMock->expects($this->once())
            ->method('getIsCustomerNotified')
            ->willReturn(1);
        $this->commentMock->expects($this->once())
            ->method('getParentId')
            ->willReturn($creditmemoId);
        $this->commentMock->expects($this->once())
            ->method('getComment')
            ->willReturn($comment);

        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('get')
            ->with($creditmemoId)
            ->willReturn($this->creditmemoMock);
        $this->creditmemoCommentSender->expects($this->once())
            ->method('send')
            ->willThrowException(new \Exception());
        $this->loggerMock->expects($this->once())
            ->method('critical');

        $this->commentRepository->save($this->commentMock);
    }
}
