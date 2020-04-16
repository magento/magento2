<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Backend\Model\View\Result\Redirect as RedirectResult;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\Manager as MessageManager;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\Email;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Email
     */
    protected $shipmentEmail;

    /**
     * @var ActionContext|MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $response;

    /**
     * @var MessageManager|MockObject
     */
    protected $messageManager;

    /**
     * @var ObjectManager|MockObject
     */
    protected $objectManager;

    /**
     * @var BackendSession|MockObject
     */
    protected $session;

    /**
     * @var ActionFlag|MockObject
     */
    protected $actionFlag;

    /**
     * @var BackendHelper|MockObject
     */
    protected $helper;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactory;

    /**
     * @var RedirectResult|MockObject
     */
    protected $resultRedirect;

    /**
     * @var ShipmentLoader|MockObject
     */
    protected $shipmentLoader;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->shipmentLoader = $this->createPartialMock(
            ShipmentLoader::class,
            ['setOrderId', 'setShipmentId', 'setShipment', 'setTracking', 'load']
        );
        $this->context = $this->createPartialMock(
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
            ]
        );
        $this->response = $this->createPartialMock(
            ResponseInterface::class,
            ['setRedirect', 'sendResponse']
        );
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(
                [
                    'isPost',
                    'getModuleName',
                    'setModuleName',
                    'getActionName',
                    'setActionName',
                    'getParam',
                    'getCookie',
                ]
            )
            ->getMockForAbstractClass();
        $this->objectManager = $this->createPartialMock(
            ObjectManager::class,
            ['create']
        );
        $this->messageManager = $this->createPartialMock(
            MessageManager::class,
            ['addSuccess', 'addError']
        );
        $this->session = $this->createPartialMock(BackendSession::class, ['setIsUrlNotice']);
        $this->actionFlag = $this->createPartialMock(ActionFlag::class, ['get']);
        $this->helper = $this->createPartialMock(BackendHelper::class, ['getUrl']);
        $this->resultRedirect = $this->createMock(RedirectResult::class);
        $this->resultFactory = $this->createPartialMock(ResultFactory::class, ['create']);
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
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
        $orderShipment = $this->createPartialMock(
            \Magento\Sales\Model\Order\Shipment::class,
            ['load', 'save', '__wakeup']
        );
        $shipmentNotifierClassName = \Magento\Shipping\Model\ShipmentNotifier::class;
        $shipmentNotifier = $this->createPartialMock($shipmentNotifierClassName, ['notify', '__wakeup']);

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
