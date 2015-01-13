<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

/**
 * Class ViewTest
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentLoaderMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentMock;

    /**
     * @var \Magento\Backend\Model\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Shipping\Block\Adminhtml\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlag;

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

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\Shipment\View
     */
    protected $controller;

    protected function setUp()
    {
        $this->requestMock = $this->getMock(
            'Magento\Framework\App\Request\Http',
            ['getParam'],
            [],
            '',
            false
        );
        $this->shipmentLoaderMock = $this->getMock(
            'Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader',
            ['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', 'load'],
            [],
            '',
            false
        );
        $this->shipmentMock = $this->getMock(
            'Magento\Sales\Model\Order\Shipment',
            ['getIncrementId', '__wakeup'],
            [],
            '',
            false
        );
        $this->viewMock = $this->getMock(
            'Magento\Backend\Model\View',
            ['loadLayout', 'getLayout', 'renderLayout', 'getPage'],
            [],
            '',
            false
        );
        $this->responseMock = $this->getMock(
            'Magento\Framework\App\Response\Http',
            [],
            [],
            '',
            false
        );
        $this->sessionMock = $this->getMock(
            'Magento\Backend\Model\Session',
            ['setIsUrlNotice'],
            [],
            '',
            false
        );
        $this->actionFlag = $this->getMock(
            'Magento\Framework\App\ActionFlag',
            ['get'],
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
        $contextMock = $this->getMock(
            'Magento\Backend\App\Action\Context',
            ['getRequest', 'getResponse', 'getView', 'getSession', 'getActionFlag'],
            [],
            '',
            false
        );

        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())->method('getView')->will($this->returnValue($this->viewMock));
        $contextMock->expects($this->any())->method('getSession')->will($this->returnValue($this->sessionMock));
        $contextMock->expects($this->any())->method('getActionFlag')->will($this->returnValue($this->actionFlag));

        $this->controller = new \Magento\Shipping\Controller\Adminhtml\Order\Shipment\View(
            $contextMock,
            $this->shipmentLoaderMock
        );

        $this->viewMock->expects($this->any())
            ->method('getPage')
            ->willReturn($this->resultPageMock);
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);
        $this->pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($this->pageTitleMock);
    }

    /**
     * Run test execute method
     */
    public function testExecute()
    {
        $orderId = 1;
        $shipmentId = 1;
        $shipment = [];
        $tracking = [];
        $incrementId = '10000001';
        $comeFrom = true;

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('order_id')
            ->will($this->returnValue($orderId));
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('shipment_id')
            ->will($this->returnValue($shipmentId));
        $this->requestMock->expects($this->at(2))
            ->method('getParam')
            ->with('shipment')
            ->will($this->returnValue($shipment));
        $this->requestMock->expects($this->at(3))
            ->method('getParam')
            ->with('tracking')
            ->will($this->returnValue($tracking));
        $this->requestMock->expects($this->at(4))
            ->method('getParam')
            ->with('come_from')
            ->will($this->returnValue($comeFrom));
        $this->shipmentLoaderMock->expects($this->once())->method('setOrderId')->with($orderId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipmentId')->with($shipmentId);
        $this->shipmentLoaderMock->expects($this->once())->method('setShipment')->with($shipment);
        $this->shipmentLoaderMock->expects($this->once())->method('setTracking')->with($tracking);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue($this->shipmentMock));
        $this->shipmentMock->expects($this->once())->method('getIncrementId')->will($this->returnValue($incrementId));

        $menuBlockMock = $this->getMock(
            'Magento\Backend\Block\Menu',
            ['getParentItems', 'getMenuModel'],
            [],
            '',
            false
        );
        $menuBlockMock->expects($this->any())->method('getMenuModel')->will($this->returnSelf());
        $menuBlockMock->expects($this->any())
            ->method('getParentItems')
            ->with('Magento_Sales::sales_order')
            ->will($this->returnValue([]));
        $shipmentBlockMock = $this->getMock(
            'Magento\Shipping\Block\Adminhtml\View',
            ['updateBackButtonUrl'],
            [],
            '',
            false
        );
        $layoutMock = $this->getMock(
            'Magento\Framework\View\Layout',
            ['getBlock'],
            [],
            '',
            false
        );
        $shipmentBlockMock->expects($this->once())
            ->method('updateBackButtonUrl')
            ->with($comeFrom)
            ->will($this->returnSelf());
        $layoutMock->expects($this->at(0))
            ->method('getBlock')
            ->with('sales_shipment_view')
            ->will($this->returnValue($shipmentBlockMock));
        $layoutMock->expects($this->at(1))
            ->method('getBlock')
            ->with('menu')
            ->will($this->returnValue($menuBlockMock));

        $this->viewMock->expects($this->once())->method('loadLayout')->will($this->returnSelf());
        $this->viewMock->expects($this->any())->method('getLayout')->will($this->returnValue($layoutMock));
        $this->viewMock->expects($this->once())->method('renderLayout')->will($this->returnSelf());

        $this->assertNull($this->controller->execute());
    }

    /**
     * Run test execute method (no shipment)
     */
    public function testExecuteNoShipment()
    {
        $orderId = 1;
        $shipmentId = 1;
        $shipment = [];
        $tracking = [];

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('order_id')
            ->will($this->returnValue($orderId));
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('shipment_id')
            ->will($this->returnValue($shipmentId));
        $this->requestMock->expects($this->at(2))
            ->method('getParam')
            ->with('shipment')
            ->will($this->returnValue($shipment));
        $this->requestMock->expects($this->at(3))
            ->method('getParam')
            ->with('tracking')
            ->will($this->returnValue($tracking));
        $this->shipmentLoaderMock->expects($this->once())
            ->method('setOrderId')
            ->with($orderId);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('setShipmentId')
            ->with($shipmentId);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('setShipment')
            ->with($shipment);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('setTracking')
            ->with($tracking);
        $this->shipmentLoaderMock->expects($this->once())
            ->method('load')
            ->will($this->returnValue(false));
        $this->actionFlag->expects($this->once())
            ->method('get')
            ->with('', \Magento\Backend\App\AbstractAction::FLAG_IS_URLS_CHECKED)
            ->will($this->returnValue(true));
        $this->sessionMock->expects($this->once())
            ->method('setIsUrlNotice')
            ->with(true);

        $this->assertNull($this->controller->execute());
    }
}
