<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

use \Magento\Shipping\Controller\Adminhtml\Order\Shipment\Email;

use Magento\Framework\App\Action\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class EmailTest
 *
 * @package Magento\Shipping\Controller\Adminhtml\Order\Shipment
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Email
     */
    protected $shipmentEmail;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentLoader;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->shipmentLoader = $this->getMock(
            \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader::class,
            ['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', 'load'],
            [],
            '',
            false
        );
        $this->context = $this->getMock(
            \Magento\Backend\App\Action\Context::class,
            [
                'getRequest',
                'getResponse',
                'getMessageManager',
                'getRedirect',
                'getObjectManager',
                'getSession',
                'getActionFlag',
                'getHelper',
                'getResultFactory'
            ],
            [],
            '',
            false
        );
        $this->response = $this->getMock(
            \Magento\Framework\App\ResponseInterface::class,
            ['setRedirect', 'sendResponse'],
            [],
            '',
            false
        );
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods([
                'isPost',
                'getModuleName',
                'setModuleName',
                'getActionName',
                'setActionName',
                'getParam',
                'getCookie',
            ])
            ->getMockForAbstractClass();
        $this->objectManager = $this->getMock(
            \Magento\Framework\ObjectManager\ObjectManager::class,
            ['create'],
            [],
            '',
            false
        );
        $this->messageManager = $this->getMock(
            \Magento\Framework\Message\Manager::class,
            ['addSuccess', 'addError'],
            [],
            '',
            false
        );
        $this->session = $this->getMock(\Magento\Backend\Model\Session::class, ['setIsUrlNotice'], [], '', false);
        $this->actionFlag = $this->getMock(\Magento\Framework\App\ActionFlag::class, ['get'], [], '', false);
        $this->helper = $this->getMock(\Magento\Backend\Helper\Data::class, ['getUrl'], [], '', false);
        $this->resultRedirect = $this->getMock(\Magento\Backend\Model\View\Result\Redirect::class, [], [], '', false);
        $this->resultFactory = $this->getMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirect);

        $this->context->expects($this->once())->method('getMessageManager')->willReturn($this->messageManager);
        $this->context->expects($this->once())->method('getRequest')->willReturn($this->request);
        $this->context->expects($this->once())->method('getResponse')->willReturn($this->response);
        $this->context->expects($this->once())->method('getObjectManager')->willReturn($this->objectManager);
        $this->context->expects($this->once())->method('getSession')->willReturn($this->session);
        $this->context->expects($this->once())->method('getActionFlag')->willReturn($this->actionFlag);
        $this->context->expects($this->once())->method('getHelper')->willReturn($this->helper);
        $this->context->expects($this->once())->method('getResultFactory')->willReturn($this->resultFactory);

        $this->shipmentEmail = $objectManagerHelper->getObject(
            \Magento\Shipping\Controller\Adminhtml\Order\Shipment\Email::class,
            [
                'context' => $this->context,
                'shipmentLoader' => $this->shipmentLoader,
                'request' => $this->request,
                'response' => $this->response
            ]
        );
    }

    public function testEmail()
    {
        $shipmentId = 1000012;
        $orderId = 10003;
        $tracking = [];
        $shipment = ['items' => []];
        $orderShipment = $this->getMock(
            \Magento\Sales\Model\Order\Shipment::class,
            ['load', 'save', '__wakeup'],
            [],
            '',
            false
        );
        $shipmentNotifierClassName = \Magento\Shipping\Model\ShipmentNotifier::class;
        $shipmentNotifier = $this->getMock($shipmentNotifierClassName, ['notify', '__wakeup'], [], '', false);

        $this->request->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        ['order_id', null, $orderId],
                        ['shipment_id', null, $shipmentId],
                        ['shipment', null, $shipment],
                        ['tracking', null, $tracking],
                    ]
                )
            );
        $this->shipmentLoader->expects($this->once())
            ->method('setShipmentId')
            ->with($shipmentId);
        $this->shipmentLoader->expects($this->once())
            ->method('setOrderId')
            ->with($orderId);
        $this->shipmentLoader->expects($this->once())
            ->method('setShipment')
            ->with($shipment);
        $this->shipmentLoader->expects($this->once())
            ->method('setTracking')
            ->with($tracking);
        $this->shipmentLoader->expects($this->once())
            ->method('load')
            ->will($this->returnValue($orderShipment));
        $orderShipment->expects($this->once())
            ->method('save')
            ->will($this->returnSelf());
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with($shipmentNotifierClassName)
            ->will($this->returnValue($shipmentNotifier));
        $shipmentNotifier->expects($this->once())
            ->method('notify')
            ->with($orderShipment)
            ->will($this->returnValue(true));
        $this->messageManager->expects($this->once())
            ->method('addSuccess')
            ->with('You sent the shipment.');
        $path = '*/*/view';
        $arguments = ['shipment_id' => $shipmentId];
        $this->prepareRedirect($path, $arguments, 0);

        $this->shipmentEmail->execute();
        $this->assertEquals($this->response, $this->shipmentEmail->getResponse());
    }

    /**
     * @param string $path
     * @param array $arguments
     * @param int $index
     */
    protected function prepareRedirect($path, $arguments, $index)
    {
        $this->actionFlag->expects($this->any())
            ->method('get')
            ->with('', 'check_url_settings')
            ->will($this->returnValue(true));
        $this->session->expects($this->any())
            ->method('setIsUrlNotice')
            ->with(true);
        $this->resultRedirect->expects($this->at($index))
            ->method('setPath')
            ->with($path, ['shipment_id' => $arguments['shipment_id']]);
    }
}
