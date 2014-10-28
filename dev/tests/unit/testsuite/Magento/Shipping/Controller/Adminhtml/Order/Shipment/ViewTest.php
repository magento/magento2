<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @var \Magento\Framework\App\Action\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $titleMock;

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
        $this->titleMock = $this->getMock(
            'Magento\Framework\App\Action\Title',
            ['add'],
            [],
            '',
            false
        );
        $this->viewMock = $this->getMock(
            'Magento\Backend\Model\View',
            ['loadLayout', 'getLayout', 'renderLayout'],
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
        $contextMock = $this->getMock(
            'Magento\Backend\App\Action\Context',
            ['getRequest', 'getResponse', 'getTitle', 'getView', 'getSession', 'getActionFlag'],
            [],
            '',
            false
        );

        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())->method('getTitle')->will($this->returnValue($this->titleMock));
        $contextMock->expects($this->any())->method('getView')->will($this->returnValue($this->viewMock));
        $contextMock->expects($this->any())->method('getSession')->will($this->returnValue($this->sessionMock));
        $contextMock->expects($this->any())->method('getActionFlag')->will($this->returnValue($this->actionFlag));

        $this->controller = new \Magento\Shipping\Controller\Adminhtml\Order\Shipment\View(
            $contextMock,
            $this->shipmentLoaderMock
        );
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
        $this->titleMock->expects($this->at(0))->method('add')->with('Shipments')->will($this->returnSelf());
        $this->titleMock->expects($this->at(1))->method('add')->with('#' . $incrementId)->will($this->returnSelf());

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
