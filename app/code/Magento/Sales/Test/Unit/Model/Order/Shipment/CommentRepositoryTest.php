<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
     * @var \PHPUnit_Framework_MockObject_MockObject|ShipmentCommentResourceInterface
     */
    private $commentResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ShipmentCommentInterfaceFactory
     */
    private $commentFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ShipmentCommentSearchResultInterfaceFactory
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
     * @var \PHPUnit_Framework_MockObject_MockObject|ShipmentCommentSender
     */
    private $shipmentCommentSender;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ShipmentRepositoryInterface
     */
    private $shipmentRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Shipment
     */
    private $shipmentMock;

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
        $this->commentResource = $this->getMockBuilder(ShipmentCommentResourceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->commentFactory = $this->getMockBuilder(ShipmentCommentInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultFactory = $this->getMockBuilder(ShipmentCommentSearchResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shipmentRepositoryMock = $this->getMockBuilder(ShipmentRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shipmentCommentSender = $this->getMockBuilder(ShipmentCommentSender::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();

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
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save the shipment comment.
     */
    public function testSaveWithException()
    {
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
            ->method('warning');

        $this->commentRepository->save($this->commentMock);
    }
}
