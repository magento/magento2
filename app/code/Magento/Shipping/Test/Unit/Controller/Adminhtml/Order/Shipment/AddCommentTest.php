<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\Page;
use Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSender;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Block\Adminhtml\View\Comments;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\AddComment;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddCommentTest extends TestCase
{
    /**
     * @var ShipmentLoader|MockObject
     */
    protected $shipmentLoaderMock;

    /**
     * @var ShipmentCommentSender|MockObject
     */
    protected $shipmentCommentSenderMock;

    /**
     * @var Http|MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|MockObject
     */
    protected $responseMock;

    /**
     * @var MockObject
     */
    protected $resultPageMock;

    /**
     * @var Shipment|MockObject
     */
    protected $shipmentMock;

    /**
     * @var ViewInterface|MockObject
     */
    protected $viewInterfaceMock;

    /**
     * @var LayoutFactory|MockObject
     */
    protected $resultLayoutFactoryMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var AddComment
     */
    protected $controller;

    protected function setUp(): void
    {
        $this->shipmentLoaderMock = $this->getMockBuilder(ShipmentLoader::class)
            ->addMethods(['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', '__wakeup'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->shipmentCommentSenderMock = $this->getMockBuilder(ShipmentCommentSender::class)
            ->addMethods(['__wakeup'])
            ->onlyMethods(['send'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->addMethods(['__wakeup'])
            ->onlyMethods(['getParam', 'getPost', 'setParam'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->createPartialMock(
            \Magento\Framework\App\Response\Http::class,
            ['setBody', 'representJson', '__wakeup']
        );
        $this->resultLayoutFactoryMock = $this->createPartialMock(
            LayoutFactory::class,
            ['create']
        );

        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->shipmentMock = $this->createPartialMock(
            Shipment::class,
            ['save', 'addComment', '__wakeup']
        );
        $this->viewInterfaceMock = $this->getMockForAbstractClass(ViewInterface::class);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $contextMock = $this->getMockBuilder(Context::class)
            ->addMethods(['getTitle', '__wakeup'])
            ->onlyMethods(['getRequest', 'getResponse', 'getView', 'getObjectManager'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewInterfaceMock->expects($this->any())->method('getPage')->willReturn(
            $this->resultPageMock
        );

        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->any())->method('getView')->willReturn($this->viewInterfaceMock);
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);

        $this->controller = new AddComment(
            $contextMock,
            $this->shipmentLoaderMock,
            $this->shipmentCommentSenderMock,
            $this->resultLayoutFactoryMock
        );
    }

    /**
     * Processing section runtime errors
     *
     * @return void
     */
    protected function exceptionResponse()
    {
        $dataMock = $this->createPartialMock(Data::class, ['jsonEncode']);

        $this->objectManagerMock->expects($this->once())->method('get')->willReturn($dataMock);
        $dataMock->expects($this->once())->method('jsonEncode')->willReturn('{json-data}');
        $this->responseMock->expects($this->once())->method('representJson')->with('{json-data}');
    }

    /**
     * Run test execute method
     */
    public function testExecute()
    {
        $data = ['comment' => 'comment'];
        $result = 'result-html';
        $orderId = 1;
        $shipmentId = 1;
        $shipment = [];
        $tracking = [];

        $resultLayoutMock = $this->getMockBuilder(Layout::class)
            ->addMethods(['getBlock'])
            ->onlyMethods(['getDefaultLayoutHandle', 'addDefaultHandle', 'getLayout'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock->expects($this->once())->method('setParam')->with('shipment_id', $shipmentId);
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('comment')
            ->willReturn($data);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $shipmentId],
                    ['order_id', null, $orderId],
                    ['shipment_id', null, $shipmentId],
                    ['shipment', null, $shipment],
                    ['tracking', null, $tracking],
                ]
            );
        $this->shipmentLoaderMock->expects($this->once())->method('setOrderId')->with($orderId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipmentId')->with($shipmentId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipment')->with($shipment);
        $this->shipmentLoaderMock->expects($this->once())->method('setTracking')->with($tracking);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willReturn($this->shipmentMock);
        $this->shipmentMock->expects($this->once())->method('addComment');
        $this->shipmentCommentSenderMock->expects($this->once())->method('send');
        $this->shipmentMock->expects($this->once())->method('save');
        $layoutMock = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['getBlock']);
        $blockMock = $this->createPartialMock(Comments::class, ['toHtml']);
        $blockMock->expects($this->once())->method('toHtml')->willReturn($result);
        $layoutMock->expects($this->once())->method('getBlock')
            ->with('shipment_comments')->willReturn($blockMock);
        $resultLayoutMock->expects($this->once())->method('getLayout')->willReturn($layoutMock);
        $resultLayoutMock->expects($this->once())->method('addDefaultHandle');
        $this->resultLayoutFactoryMock->expects($this->once())->method('create')
            ->willReturn($resultLayoutMock);
        $this->responseMock->expects($this->once())->method('setBody')->with($result);

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (exception load shipment)
     */
    public function testExecuteLoadException()
    {
        $orderId = 1;
        $shipmentId = 1;
        $shipment = [];
        $tracking = [];
        $data = ['comment' => 'comment'];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $shipmentId],
                    ['order_id', null, $orderId],
                    ['shipment_id', null, $shipmentId],
                    ['shipment', null, $shipment],
                    ['tracking', null, $tracking],
                ]
            );
        $this->requestMock->expects($this->once())->method('setParam')->with('shipment_id', $shipmentId);
        $this->requestMock->expects($this->once())->method('getPost')->with('comment')->willReturn($data);
        $this->shipmentLoaderMock->expects($this->once())->method('setOrderId')->with($orderId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipmentId')->with($shipmentId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipment')->with($shipment);
        $this->shipmentLoaderMock->expects($this->once())->method('setTracking')->with($tracking);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willThrowException(new LocalizedException(__('message')));
        $this->exceptionResponse();

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (empty data comment)
     */
    public function testEmptyCommentData()
    {
        $shipmentId = 1;
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($shipmentId);
        $this->requestMock->expects($this->once())->method('setParam')->with('shipment_id', $shipmentId);
        $this->requestMock->expects($this->once())->method('getPost')->with('comment')->willReturn([]);
        $this->exceptionResponse();

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (save exception)
     */
    public function testExecuteExceptionSave()
    {
        $data = ['comment' => 'comment'];
        $orderId = 1;
        $shipmentId = 1;
        $shipment = [];
        $tracking = [];

        $this->requestMock->expects($this->once())->method('setParam')->with('shipment_id', $shipmentId);
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('comment')
            ->willReturn($data);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, $shipmentId],
                    ['order_id', null, $orderId],
                    ['shipment_id', null, $shipmentId],
                    ['shipment', null, $shipment],
                    ['tracking', null, $tracking],
                ]
            );
        $this->shipmentLoaderMock->expects($this->once())->method('setOrderId')->with($orderId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipmentId')->with($shipmentId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipment')->with($shipment);
        $this->shipmentLoaderMock->expects($this->once())->method('setTracking')->with($tracking);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willReturn($this->shipmentMock);
        $this->shipmentMock->expects($this->once())->method('addComment');
        $this->shipmentCommentSenderMock->expects($this->once())->method('send');
        $this->shipmentMock->expects($this->once())->method('save')->willThrowException(new \Exception());
        $this->exceptionResponse();

        $this->assertNull($this->controller->execute());
    }
}
