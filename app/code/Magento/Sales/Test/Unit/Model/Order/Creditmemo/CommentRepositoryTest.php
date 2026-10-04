<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Creditmemo;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoCommentInterfaceFactory;
use Magento\Sales\Api\Data\CreditmemoCommentSearchResultInterfaceFactory;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Comment;
use Magento\Sales\Model\Order\Creditmemo\CommentRepository;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoCommentSender;
use Magento\Sales\Model\Spi\CreditmemoCommentResourceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommentRepositoryTest extends TestCase
{

    /**
     * @var MockObject|CreditmemoCommentResourceInterface
     */
    private $commentResource;

    /**
     * @var MockObject|CreditmemoCommentInterfaceFactory
     */
    private $commentFactory;

    /**
     * @var MockObject|CreditmemoCommentSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var MockObject|CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CommentRepository
     */
    private $commentRepository;

    /**
     * @var MockObject|CreditmemoCommentSender
     */
    private $creditmemoCommentSender;

    /**
     * @var MockObject|CreditmemoRepositoryInterface
     */
    private $creditmemoRepositoryMock;

    /**
     * @var MockObject|Creditmemo
     */
    private $creditmemoMock;

    /**
     * @var MockObject|Comment
     */
    private $commentMock;

    /**
     * @var MockObject|LoggerInterface
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->commentResource = $this->createMock(CreditmemoCommentResourceInterface::class);
        $this->commentFactory = $this->createMock(CreditmemoCommentInterfaceFactory::class);
        $this->searchResultFactory = $this->createMock(CreditmemoCommentSearchResultInterfaceFactory::class);
        $this->collectionProcessor = $this->createMock(CollectionProcessorInterface::class);
        $this->creditmemoRepositoryMock = $this->createMock(CreditmemoRepositoryInterface::class);
        $this->creditmemoCommentSender = $this->createMock(CreditmemoCommentSender::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->creditmemoMock = $this->createMock(Creditmemo::class);
        $this->commentMock = $this->createMock(Comment::class);

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

    public function testSaveWithException()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->expectExceptionMessage('Could not save the creditmemo comment.');
        $this->commentResource->expects($this->once())
            ->method('save')
            ->with($this->commentMock)
            ->willThrowException(
                new CouldNotSaveException(__('Could not save the creditmemo comment.'))
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
