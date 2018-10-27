<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
     * @var \PHPUnit_Framework_MockObject_MockObject|InvoiceCommentResourceInterface
     */
    private $commentResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|InvoiceCommentInterfaceFactory
     */
    private $commentFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|InvoiceCommentSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CommentRepository
     */
    private $commentRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|InvoiceCommentSender
     */
    private $invoiceCommentSender;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|InvoiceRepositoryInterface
     */
    private $invoiceRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Invoice
     */
    private $invoiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Comment
     */
    private $commentMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    private $loggerMock;

    protected function setUp()
    {
        $this->commentResource = $this->getMockBuilder(InvoiceCommentResourceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->commentFactory = $this->getMockBuilder(InvoiceCommentInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultFactory = $this->getMockBuilder(InvoiceCommentSearchResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceRepositoryMock = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceCommentSender = $this->getMockBuilder(InvoiceCommentSender::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();

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
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save the invoice comment.
     */
    public function testSaveWithException()
    {
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
            ->method('warning');

        $this->commentRepository->save($this->commentMock);
    }
}
