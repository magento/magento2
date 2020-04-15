<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Shipment;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\Data\ShipmentCommentInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentCommentSearchResultInterfaceFactory;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Comment;
use Magento\Sales\Model\Order\Shipment\CommentRepository;
use Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSender;
use Magento\Sales\Model\Spi\ShipmentCommentResourceInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CommentRepositoryTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommentRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ShipmentCommentResourceInterface
     */
    private $commentResource;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ShipmentCommentInterfaceFactory
     */
    private $commentFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ShipmentCommentSearchResultInterfaceFactory
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
     * @var \PHPUnit\Framework\MockObject\MockObject|ShipmentCommentSender
     */
    private $shipmentCommentSender;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ShipmentRepositoryInterface
     */
    private $shipmentRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Shipment
     */
    private $shipmentMock;

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
        $this->commentResource = $this->getMockBuilder(ShipmentCommentResourceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->commentFactory = $this->getMockBuilder(ShipmentCommentInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultFactory = $this->getMockBuilder(ShipmentCommentSearchResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->shipmentRepositoryMock = $this->getMockBuilder(ShipmentRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->shipmentCommentSender = $this->getMockBuilder(ShipmentCommentSender::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMockForAbstractClass();

        $this->shipmentMock = $this->getMockBuilder(Shipment::class)->disableOriginalConstructor()->getMock();
        $this->commentMock = $this->getMockBuilder(Comment::class)->disableOriginalConstructor()->getMock();

        $this->commentRepository = new CommentRepository(
            $this->commentResource,
            $this->commentFactory,
            $this->searchResultFactory,
            $this->collectionProcessor,
            $this->shipmentCommentSender,
            $this->shipmentRepositoryMock,
            $this->loggerMock
        );
    }

    public function testSave()
    {
        $comment = "Comment text";
        $shipmentId = 123;
        $this->commentResource->expects($this->once())
            ->method('save')
            ->with($this->commentMock)
            ->willReturnSelf();
        $this->commentMock->expects($this->once())
            ->method('getIsCustomerNotified')
            ->willReturn(1);
        $this->commentMock->expects($this->once())
            ->method('getParentId')
            ->willReturn($shipmentId);
        $this->commentMock->expects($this->once())
            ->method('getComment')
            ->willReturn($comment);

        $this->shipmentRepositoryMock->expects($this->once())
            ->method('get')
            ->with($shipmentId)
            ->willReturn($this->shipmentMock);
        $this->shipmentCommentSender->expects($this->once())
            ->method('send')
            ->with($this->shipmentMock, true, $comment);
        $this->assertEquals($this->commentMock, $this->commentRepository->save($this->commentMock));
    }

    /**
     */
    public function testSaveWithException()
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->expectExceptionMessage('Could not save the shipment comment.');

        $this->commentResource->expects($this->once())
            ->method('save')
            ->with($this->commentMock)
            ->willThrowException(
                new \Magento\Framework\Exception\CouldNotSaveException(__('Could not save the shipment comment.'))
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

        $this->shipmentRepositoryMock->expects($this->once())
            ->method('get')
            ->with($creditmemoId)
            ->willReturn($this->shipmentMock);
        $this->shipmentCommentSender->expects($this->once())
            ->method('send')
            ->willThrowException(new \Exception());
        $this->loggerMock->expects($this->once())
            ->method('critical');

        $this->commentRepository->save($this->commentMock);
    }
}
