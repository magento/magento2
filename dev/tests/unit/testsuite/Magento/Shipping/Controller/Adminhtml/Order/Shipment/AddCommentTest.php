<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

/**
 * Class AddCommentTest
 */
class AddCommentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentLoaderMock;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentSenderMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $titleMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentMock;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewInterfaceMock;

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
        $this->shipmentLoaderMock = $this->getMock(
            'Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader',
            ['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', 'load', '__wakeup'],
            [],
            '',
            false
        );
        $this->shipmentSenderMock = $this->getMock(
            'Magento\Sales\Model\Order\Email\Sender\ShipmentSender',
            ['send', '__wakeup'],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMock(
            'Magento\Framework\App\Request\Http',
            ['getParam', 'getPost', 'setParam', '__wakeup'],
            [],
            '',
            false
        );
        $this->responseMock = $this->getMock(
            'Magento\Framework\App\Response\Http',
            ['setBody', 'representJson', '__wakeup'],
            [],
            '',
            false
        );
        $this->titleMock = $this->getMock(
            'Magento\Framework\View\Page\Title',
            ['prepend', '__wakeup'],
            [],
            '',
            false
        );

        $this->resultPageMock = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->shipmentMock = $this->getMock(
            'Magento\Sales\Model\Order\Shipment',
            ['save', 'addComment', '__wakeup'],
            [],
            '',
            false
        );
        $this->viewInterfaceMock = $this->getMock(
            'Magento\Framework\App\ViewInterface',
            [],
            [],
            '',
            false
        );
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');

        $contextMock = $this->getMock(
            'Magento\Backend\App\Action\Context',
            ['getRequest', 'getResponse', 'getTitle', 'getView', 'getObjectManager', '__wakeup'],
            [],
            '',
            false
        );
        $this->viewInterfaceMock->expects($this->any())->method('getPage')->will(
            $this->returnValue($this->resultPageMock)
        );
        $this->resultPageMock->expects($this->any())->method('getConfig')->will(
            $this->returnValue($this->pageConfigMock)
        );

        $this->pageConfigMock->expects($this->any())->method('getTitle')->will($this->returnValue($this->titleMock));

        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())->method('getTitle')->will($this->returnValue($this->titleMock));
        $contextMock->expects($this->any())->method('getView')->will($this->returnValue($this->viewInterfaceMock));
        $contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));

        $this->controller = new \Magento\Shipping\Controller\Adminhtml\Order\Shipment\AddComment(
            $contextMock,
            $this->shipmentLoaderMock,
            $this->shipmentSenderMock
        );
    }

    /**
     * Processing section runtime errors
     *
     * @return void
     */
    protected function exceptionResponse()
    {
        $dataMock = $this->getMock(
            'Magento\Core\Helper\Data',
            ['jsonEncode'],
            [],
            '',
            false
        );

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

        $layoutMock = $this->getMock('Magento\Framework\View\Layout', ['getBlock'], [], '', false);
        $blockMock = $this->getMock('Magento\Shipping\Block\Adminhtml\View\Comments', ['toHtml'], [], '', false);

        $this->requestMock->expects($this->once())->method('setParam')->with('shipment_id', $shipmentId);
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('comment')
            ->will($this->returnValue($data));
        $this->titleMock->expects($this->once())->method('prepend');
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
        $this->shipmentSenderMock->expects($this->once())->method('send');
        $this->shipmentMock->expects($this->once())->method('save');
        $this->viewInterfaceMock->expects($this->once())->method('loadLayout')->with(false);
        $this->viewInterfaceMock->expects($this->once())->method('getLayout')->will($this->returnValue($layoutMock));
        $layoutMock->expects($this->once())->method('getBlock')->will($this->returnValue($blockMock));
        $blockMock->expects($this->once())->method('toHtml')->will($this->returnValue($result));
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
            ->will($this->throwException(new \Magento\Framework\Model\Exception()));
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
        $this->shipmentSenderMock->expects($this->once())->method('send');
        $this->shipmentMock->expects($this->once())->method('save')->will($this->throwException(new \Exception()));
        $this->exceptionResponse();

        $this->assertNull($this->controller->execute());
    }
}
