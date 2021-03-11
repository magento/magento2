<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Invoice;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\Data\InvoiceCommentInterfaceFactory;
use Magento\Sales\Api\Data\InvoiceCommentSearchResultInterfaceFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Comment;
use Magento\Sales\Model\Order\Invoice\CommentRepository;
use Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender;
use Magento\Sales\Model\Spi\InvoiceCommentResourceInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CommentRepositoryTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommentRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|InvoiceCommentResourceInterface
     */
    private $commentResource;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|InvoiceCommentInterfaceFactory
     */
    private $commentFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|InvoiceCommentSearchResultInterfaceFactory
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
     * @var \PHPUnit\Framework\MockObject\MockObject|InvoiceCommentSender
     */
    private $invoiceCommentSender;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|InvoiceRepositoryInterface
     */
    private $invoiceRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Invoice
     */
    private $invoiceMock;

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
        $this->commentResource = $this->getMockBuilder(InvoiceCommentResourceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->commentFactory = $this->getMockBuilder(InvoiceCommentInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultFactory = $this->getMockBuilder(InvoiceCommentSearchResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->invoiceRepositoryMock = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->invoiceCommentSender = $this->getMockBuilder(InvoiceCommentSender::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMockForAbstractClass();

        $this->invoiceMock = $this->getMockBuilder(Invoice::class)->disableOriginalConstructor()->getMock();
        $this->commentMock = $this->getMockBuilder(Comment::class)->disableOriginalConstructor()->getMock();

        $this->commentRepository = new CommentRepository(
            $this->commentResource,
            $this->commentFactory,
            $this->searchResultFactory,
            $this->collectionProcessor,
            $this->invoiceCommentSender,
            $this->invoiceRepositoryMock,
            $this->loggerMock
        );
    }

    public function testSave()
    {
        $comment = "Comment text";
        $invoiceId = 123;
        $this->commentResource->expects($this->once())
            ->method('save')
            ->with($this->commentMock)
            ->willReturnSelf();
        $this->commentMock->expects($this->once())
            ->method('getIsCustomerNotified')
            ->willReturn(1);
        $this->commentMock->expects($this->once())
            ->method('getParentId')
            ->willReturn($invoiceId);
        $this->commentMock->expects($this->once())
            ->method('getComment')
            ->willReturn($comment);

        $this->invoiceRepositoryMock->expects($this->once())
            ->method('get')
            ->with($invoiceId)
            ->willReturn($this->invoiceMock);
        $this->invoiceCommentSender->expects($this->once())
            ->method('send')
            ->with($this->invoiceMock, true, $comment)
            ->willReturn(true);
        $this->commentRepository->save($this->commentMock);
    }

    /**
     */
    public function testSaveWithException()
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->expectExceptionMessage('Could not save the invoice comment.');

        $this->commentResource->expects($this->once())
            ->method('save')
            ->with($this->commentMock)
            ->willThrowException(
                new \Magento\Framework\Exception\CouldNotSaveException(__('Could not save the invoice comment.'))
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

        $this->invoiceRepositoryMock->expects($this->once())
            ->method('get')
            ->with($creditmemoId)
            ->willReturn($this->invoiceMock);
        $this->invoiceCommentSender->expects($this->once())
            ->method('send')
            ->willThrowException(new \Exception());
        $this->loggerMock->expects($this->once())
            ->method('critical');

        $this->commentRepository->save($this->commentMock);
    }
}
