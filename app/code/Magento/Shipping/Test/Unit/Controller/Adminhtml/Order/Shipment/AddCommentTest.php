<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

/**
 * Class AddCommentTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddCommentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentLoaderMock;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSender|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentCommentSenderMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentMock;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewInterfaceMock;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultLayoutFactoryMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\Shipment\AddComment
     */
    protected $controller;

    protected function setUp()
    {
        $this->shipmentLoaderMock = $this->createPartialMock(
            \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader::class,
            ['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', 'load', '__wakeup']
        );
        $this->shipmentCommentSenderMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSender::class,
            ['send', '__wakeup']
        );
        $this->requestMock = $this->createPartialMock(
            \Magento\Framework\App\Request\Http::class,
            ['getParam', 'getPost', 'setParam', '__wakeup']
        );
        $this->responseMock = $this->createPartialMock(
            \Magento\Framework\App\Response\Http::class,
            ['setBody', 'representJson', '__wakeup']
        );
        $this->resultLayoutFactoryMock = $this->createPartialMock(
            \Magento\Framework\View\Result\LayoutFactory::class,
            ['create']
        );

        $this->resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->shipmentMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Shipment::class,
            ['save', 'addComment', '__wakeup']
        );
        $this->viewInterfaceMock = $this->createMock(\Magento\Framework\App\ViewInterface::class);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $contextMock = $this->createPartialMock(
            \Magento\Backend\App\Action\Context::class,
            ['getRequest', 'getResponse', 'getTitle', 'getView', 'getObjectManager', '__wakeup']
        );
        $this->viewInterfaceMock->expects($this->any())->method('getPage')->will(
            $this->returnValue($this->resultPageMock)
        );

        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())->method('getView')->will($this->returnValue($this->viewInterfaceMock));
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));

        $this->controller = new \Magento\Shipping\Controller\Adminhtml\Order\Shipment\AddComment(
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
        $dataMock = $this->createPartialMock(\Magento\Framework\Json\Helper\Data::class, ['jsonEncode']);

        $this->objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($dataMock));
        $dataMock->expects($this->once())->method('jsonEncode')->will($this->returnValue('{json-data}'));
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

        $resultLayoutMock = $this->createPartialMock(
            \Magento\Framework\View\Result\Layout::class,
            ['getBlock', 'getDefaultLayoutHandle', 'addDefaultHandle', 'getLayout']
        );

        $this->requestMock->expects($this->once())->method('setParam')->with('shipment_id', $shipmentId);
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('comment')
            ->will($this->returnValue($data));
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        ['id', null, $shipmentId],
                        ['order_id', null, $orderId],
                        ['shipment_id', null, $shipmentId],
                        ['shipment', null, $shipment],
                        ['tracking', null, $tracking],
                    ]
                )
            );
        $this->shipmentLoaderMock->expects($this->once())->method('setOrderId')->with($orderId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipmentId')->with($shipmentId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipment')->with($shipment);
        $this->shipmentLoaderMock->expects($this->once())->method('setTracking')->with($tracking);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->shipmentMock));
        $this->shipmentMock->expects($this->once())->method('addComment');
        $this->shipmentCommentSenderMock->expects($this->once())->method('send');
        $this->shipmentMock->expects($this->once())->method('save');
        $layoutMock = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['getBlock']);
        $blockMock = $this->createPartialMock(\Magento\Shipping\Block\Adminhtml\View\Comments::class, ['toHtml']);
        $blockMock->expects($this->once())->method('toHtml')->willReturn($result);
        $layoutMock->expects($this->once())->method('getBlock')
            ->with('shipment_comments')->willReturn($blockMock);
        $resultLayoutMock->expects($this->once())->method('getLayout')->willReturn($layoutMock);
        $resultLayoutMock->expects($this->once())->method('addDefaultHandle');
        $this->resultLayoutFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($resultLayoutMock));
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
            ->will(
                $this->returnValueMap(
                    [
                        ['id', null, $shipmentId],
                        ['order_id', null, $orderId],
                        ['shipment_id', null, $shipmentId],
                        ['shipment', null, $shipment],
                        ['tracking', null, $tracking],
                    ]
                )
            );
        $this->requestMock->expects($this->once())->method('setParam')->with('shipment_id', $shipmentId);
        $this->requestMock->expects($this->once())->method('getPost')->with('comment')->will($this->returnValue($data));
        $this->shipmentLoaderMock->expects($this->once())->method('setOrderId')->with($orderId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipmentId')->with($shipmentId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipment')->with($shipment);
        $this->shipmentLoaderMock->expects($this->once())->method('setTracking')->with($tracking);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('message')));
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
            ->will($this->returnValue($shipmentId));
        $this->requestMock->expects($this->once())->method('setParam')->with('shipment_id', $shipmentId);
        $this->requestMock->expects($this->once())->method('getPost')->with('comment')->will($this->returnValue([]));
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
            ->will($this->returnValue($data));
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        ['id', null, $shipmentId],
                        ['order_id', null, $orderId],
                        ['shipment_id', null, $shipmentId],
                        ['shipment', null, $shipment],
                        ['tracking', null, $tracking],
                    ]
                )
            );
        $this->shipmentLoaderMock->expects($this->once())->method('setOrderId')->with($orderId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipmentId')->with($shipmentId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipment')->with($shipment);
        $this->shipmentLoaderMock->expects($this->once())->method('setTracking')->with($tracking);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->shipmentMock));
        $this->shipmentMock->expects($this->once())->method('addComment');
        $this->shipmentCommentSenderMock->expects($this->once())->method('send');
        $this->shipmentMock->expects($this->once())->method('save')->will($this->throwException(new \Exception()));
        $this->exceptionResponse();

        $this->assertNull($this->controller->execute());
    }
}
