<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
/**
 * Class AddTrackTest
 *
 * @package Magento\Shipping\Controller\Adminhtml\Order\Shipment
 */
class AddTrackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentLoader;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\Shipment\AddTrack
     */
    protected $controller;

    /**
     * @var Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var  \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $view;

    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;

    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageTitleMock;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->shipmentLoader = $this->getMockBuilder('Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->context = $this->getMock(
            'Magento\Backend\App\Action\Context',
            [
                'getRequest',
                'getResponse',
                'getRedirect',
                'getObjectManager',
                'getTitle',
                'getView'
            ],
            [],
            '',
            false
        );
        $this->response = $this->getMock(
            'Magento\Framework\App\ResponseInterface',
            ['setRedirect', 'sendResponse', 'setBody'],
            [],
            '',
            false
        );
        $this->request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()->getMock();
        $this->objectManager = $this->getMock(
            'Magento\Framework\ObjectManager\ObjectManager',
            ['create', 'get'],
            [],
            '',
            false
        );
        $this->view = $this->getMock(
            'Magento\Framework\App\ViewInterface',
            [],
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
        $this->pageTitleMock = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->request));
        $this->context->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($this->response));
        $this->context->expects($this->once())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManager));
        $this->context->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));
        $this->controller = $objectManagerHelper->getObject(
            'Magento\Shipping\Controller\Adminhtml\Order\Shipment\AddTrack',
            [
                'context' => $this->context,
                'shipmentLoader' => $this->shipmentLoader,
                'request' => $this->request,
                'response' => $this->response,
                'view' => $this->view
            ]
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute()
    {
        $carrier = 'carrier';
        $number = 'number';
        $title = 'title';
        $shipmentId = 1000012;
        $orderId = 10003;
        $tracking = [];
        $shipmentData = ['items' => [], 'send_email' => ''];
        $shipment = $this->getMock(
            'Magento\Sales\Model\Order\Shipment',
            ['addTrack', '__wakeup', 'save'],
            [],
            '',
            false
        );
        $this->request->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        ['order_id', null, $orderId], ['shipment_id', null, $shipmentId],
                        ['shipment', null, $shipmentData], ['tracking', null, $tracking],
                    ]
                )
            );
        $this->request->expects($this->any())
            ->method('getPost')
            ->will(
                $this->returnValueMap(
                    [
                        ['carrier', null, $carrier],
                        ['number', null, $number],
                        ['title', null, $title],
                    ]
                )
            );
        $this->shipmentLoader->expects($this->any())
            ->method('setShipmentId')
            ->with($shipmentId);
        $this->shipmentLoader->expects($this->any())
            ->method('setOrderId')
            ->with($orderId);
        $this->shipmentLoader->expects($this->any())
            ->method('setShipment')
            ->with($shipmentData);
        $this->shipmentLoader->expects($this->any())
            ->method('setTracking')
            ->with($tracking);
        $this->shipmentLoader->expects($this->once())
            ->method('load')
            ->will($this->returnValue($shipment));
        $track = $this->getMockBuilder('Magento\Sales\Model\Order\Shipment\Track')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'setNumber', 'setCarrierCode', 'setTitle'])
            ->getMock();
        $this->objectManager->expects($this->atLeastOnce())
            ->method('create')
            ->with('Magento\Sales\Model\Order\Shipment\Track')
            ->will($this->returnValue($track));
        $track->expects($this->once())
            ->method('setNumber')
            ->with($number)
            ->will($this->returnSelf());
        $track->expects($this->once())
            ->method('setCarrierCode')
            ->with($carrier)
            ->will($this->returnSelf());
        $track->expects($this->once())
            ->method('setTitle')
            ->with($title)
            ->will($this->returnSelf());
        $this->view->expects($this->once())
            ->method('loadLayout')
            ->will($this->returnSelf());
        $layout = $this->getMock('Magento\Framework\View\Layout\Element\Layout', ['getBlock'], [], '', false);
        $menuBlock = $this->getMock('Magento\Framework\View\Element\BlockInterface', ['toHtml'], [], '', false);
        $html = 'html string';
        $this->view->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layout));
        $layout->expects($this->once())
            ->method('getBlock')
            ->with('shipment_tracking')
            ->will($this->returnValue($menuBlock));
        $menuBlock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($html));
        $shipment->expects($this->once())
            ->method('addTrack')
            ->with($this->equalTo($track))
            ->will($this->returnSelf());
        $shipment->expects($this->any())
            ->method('save')
            ->will($this->returnSelf());
        $this->view->expects($this->any())
            ->method('getPage')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);
        $this->response->expects($this->once())
            ->method('setBody')
            ->with($html);
        $this->assertNull($this->controller->execute());
    }
}
