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

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\App\Action\Context;

/**
 * Class EmailTest
 *
 * @package Magento\Shipping\Controller\Adminhtml\Order\Shipment
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
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentLoader;

    public function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->shipmentLoader = $this->getMock(
            'Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader',
            ['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', 'load'],
            [],
            '',
            false
        );
        $this->context = $this->getMock(
            'Magento\Backend\App\Action\Context',
            [
                'getRequest',
                'getResponse',
                'getMessageManager',
                'getRedirect',
                'getObjectManager',
                'getSession',
                'getActionFlag',
                'getHelper'
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
            ['create'],
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
        $this->session = $this->getMock('Magento\Backend\Model\Session', ['setIsUrlNotice'], [], '', false);
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
        $this->shipmentEmail = $objectManagerHelper->getObject(
            'Magento\Shipping\Controller\Adminhtml\Order\Shipment\Email',
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
            'Magento\Sales\Model\Order\Shipment',
            ['load', 'save', '__wakeup'],
            [],
            '',
            false
        );
        $shipmentNotifierClassName = 'Magento\Shipping\Model\ShipmentNotifier';
        $shipmentNotifier = $this->getMock($shipmentNotifierClassName, ['notify', '__wakeup'], [], '', false);

        $this->request->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        ['order_id', null, $orderId],
                        ['shipment_id', null, $shipmentId],
                        ['shipment', null, $shipment],
                        ['tracking', null, $tracking]
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

        $url = $path . '/' . (!empty($arguments) ? $arguments['shipment_id'] : '');
        $this->helper->expects($this->at($index))
            ->method('getUrl')
            ->with($path, $arguments)
            ->will($this->returnValue($url));
        $this->response->expects($this->at($index))
            ->method('setRedirect')
            ->with($url);
    }
}
 