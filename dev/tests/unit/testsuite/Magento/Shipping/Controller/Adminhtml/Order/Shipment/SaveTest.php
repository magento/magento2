<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;
/**
 * Class SaveTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentLoader;

    /**
     * @var \Magento\Shipping\Model\Shipping\LabelGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $labelGenerator;

    /**
     * @var ShipmentSender|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentSender;

    /**
     * @var Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlag;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save
     */
    protected $saveAction;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->shipmentLoader = $this->getMockBuilder('Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->labelGenerator = $this->getMockBuilder('Magento\Shipping\Model\Shipping\LabelGenerator')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->shipmentSender = $this->getMockBuilder('Magento\Sales\Model\Order\Email\Sender\ShipmentSender')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->context = $this->getMock(
            'Magento\Backend\App\Action\Context',
            [
                'getRequest', 'getResponse', 'getMessageManager', 'getRedirect',
                'getObjectManager', 'getSession', 'getActionFlag', 'getHelper'
            ],
            [],
            '',
            false
        );
        $this->response = $this->getMock(
            'Magento\Framework\App\ResponseInterface',
            ['setRedirect', 'sendResponse'],
            [],
            '',
            false
        );
        $this->request = $this->getMock(
            'Magento\Framework\App\RequestInterface',
            ['isPost', 'getModuleName', 'setModuleName', 'getActionName', 'setActionName', 'getParam', 'getCookie'],
            [],
            '',
            false
        );
        $this->objectManager = $this->getMock(
            'Magento\Framework\ObjectManager\ObjectManager',
            ['create', 'get'],
            [],
            '',
            false
        );
        $this->messageManager = $this->getMock(
            'Magento\Framework\Message\Manager',
            ['addSuccess', 'addError'],
            [],
            '',
            false
        );
        $this->session = $this->getMock(
            'Magento\Backend\Model\Session',
            ['setIsUrlNotice', 'getCommentText'],
            [],
            '',
            false
        );
        $this->actionFlag = $this->getMock('Magento\Framework\App\ActionFlag', ['get'], [], '', false);
        $this->helper = $this->getMock('\Magento\Backend\Helper\Data', ['getUrl'], [], '', false);
        $this->context->expects($this->once())
            ->method('getMessageManager')
            ->will($this->returnValue($this->messageManager));
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
            ->method('getSession')
            ->will($this->returnValue($this->session));
        $this->context->expects($this->once())
            ->method('getActionFlag')
            ->will($this->returnValue($this->actionFlag));
        $this->context->expects($this->once())
            ->method('getHelper')
            ->will($this->returnValue($this->helper));
        $this->saveAction = $objectManagerHelper->getObject(
            'Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save',
            [
                'labelGenerator' => $this->labelGenerator, 'shipmentSender' => $this->shipmentSender,
                'context' => $this->context, 'shipmentLoader' => $this->shipmentLoader,
                'request' => $this->request, 'response' => $this->response
            ]
        );
    }

    public function testExecute()
    {
        $shipmentId = 1000012;
        $orderId = 10003;
        $tracking = [];
        $shipmentData = ['items' => [], 'send_email' => ''];
        $shipment = $this->getMock(
            'Magento\Sales\Model\Order\Shipment',
            ['load', 'save', 'register', 'getOrder', 'getOrderId', '__wakeup'],
            [],
            '',
            false
        );
        $order = $this->getMock(
            'Magento\Sales\Model\Order',
            ['setCustomerNoteNotify', '__wakeup'],
            [],
            '',
            false
        );

        $this->request->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        ['order_id', null, $orderId],
                        ['shipment_id', null, $shipmentId],
                        ['shipment', null, $shipmentData],
                        ['tracking', null, $tracking],
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
        $shipment->expects($this->once())
            ->method('register')
            ->will($this->returnSelf());
        $shipment->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));
        $order->expects($this->once())
            ->method('setCustomerNoteNotify')
            ->with(false);
        $this->labelGenerator->expects($this->any())
            ->method('create')
            ->with($shipment, $this->request)
            ->will($this->returnValue(true));
        $saveTransaction = $this->getMockBuilder('Magento\Framework\DB\Transaction')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $saveTransaction->expects($this->at(0))
            ->method('addObject')
            ->with($shipment)
            ->will($this->returnSelf());
        $saveTransaction->expects($this->at(1))
            ->method('addObject')
            ->with($order)
            ->will($this->returnSelf());
        $saveTransaction->expects($this->at(2))
            ->method('save');

        $this->session->expects($this->once())
            ->method('getCommentText')
            ->with(true);

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with('Magento\Framework\DB\Transaction')
            ->will($this->returnValue($saveTransaction));
        $this->objectManager->expects($this->once())
            ->method('get')
            ->with('Magento\Backend\Model\Session')
            ->will($this->returnValue($this->session));
        $path = 'sales/order/view';
        $arguments = ['order_id' => $orderId];
        $shipment->expects($this->once())
            ->method('getOrderId')
            ->will($this->returnValue($orderId));
        $this->prepareRedirect($path, $arguments);

        $this->saveAction->execute();
        $this->assertEquals($this->response, $this->saveAction->getResponse());
    }

    /**
     * @param string $path
     * @param array $arguments
     */
    protected function prepareRedirect($path, array $arguments = [])
    {
        $this->actionFlag->expects($this->any())
            ->method('get')
            ->with('', 'check_url_settings')
            ->will($this->returnValue(true));
        $this->session->expects($this->any())
            ->method('setIsUrlNotice')
            ->with(true);

        $url = $path . '/' . (!empty($arguments) ? $arguments['order_id'] : '');
        $this->helper->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with($path, $arguments)
            ->will($this->returnValue($url));
        $this->response->expects($this->atLeastOnce())
            ->method('setRedirect')
            ->with($url);
    }
}
