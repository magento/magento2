<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Backend\Model\Session as BackendSession;
use Magento\Backend\Model\View\Result\Redirect as RedirectResult;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\Manager as MessageManager;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\Email;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Store\Model\Store;
use Magento\Shipping\Model\ShipmentNotifier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailTest extends TestCase
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
     * @var RequestInterface|MockObject
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

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->shipmentLoader = $this->getMockBuilder(ShipmentLoader::class)
            ->addMethods(['setOrderId', 'setShipmentId', 'setShipment', 'setTracking'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->addMethods(['setRedirect'])
            ->onlyMethods(['sendResponse'])
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->onlyMethods(
                [
                    'getModuleName',
                    'setModuleName',
                    'getActionName',
                    'setActionName',
                    'getParam',
                    'getCookie',
                ]
            )->addMethods(['isPost'])
            ->getMockForAbstractClass();
        $this->objectManager = $this->createPartialMock(
            ObjectManager::class,
            ['create']
        );
        $this->messageManager = $this->createPartialMock(
            MessageManager::class,
            ['addSuccess', 'addError', 'addWarningMessage']
        );
        $this->session = $this->getMockBuilder(BackendSession::class)
            ->addMethods(['setIsUrlNotice'])
            ->disableOriginalConstructor()
            ->getMock();
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
            Email::class,
            [
                'context' => $this->context,
                'shipmentLoader' => $this->shipmentLoader,
                'request' => $this->request,
                'response' => $this->response
            ]
        );
    }

    /**
     * @return void
     */
    public function testEmail(): void
    {
        $shipmentId = 1000012;
        $orderId = 10003;
        $tracking = [];
        $shipment = ['items' => []];
        $orderShipment = $this->createPartialMock(
            Shipment::class,
            ['load', 'save', 'getStore', '__wakeup']
        );
        $shipmentNotifier = $this->createPartialMock(ShipmentNotifier::class, ['notify', '__wakeup']);

        // Mock store and config
        $store = $this->createMock(Store::class);
        $store->expects($this->once())
            ->method('getConfig')
            ->with('sales_email/shipment/enabled')
            ->willReturn(true);

        $orderShipment->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['order_id', null, $orderId],
                    ['shipment_id', null, $shipmentId],
                    ['shipment', null, $shipment],
                    ['tracking', null, $tracking]
                ]
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
            ->willReturn($orderShipment);
        $orderShipment->expects($this->once())
            ->method('save')->willReturnSelf();
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(ShipmentNotifier::class)
            ->willReturn($shipmentNotifier);
        $shipmentNotifier->expects($this->once())
            ->method('notify')
            ->with($orderShipment)
            ->willReturn(true);
        $this->messageManager->expects($this->once())
            ->method('addSuccess')
            ->with('You sent the shipment.');
        $path = '*/*/view';
        $arguments = ['shipment_id' => $shipmentId];
        $this->prepareRedirect($path, $arguments);

        $this->shipmentEmail->execute();
        $this->assertEquals($this->response, $this->shipmentEmail->getResponse());
    }

    /**
     * @return void
     */
    public function testEmailDisabled(): void
    {
        $shipmentId = 1000012;
        $orderId = 10003;
        $tracking = [];
        $shipment = ['items' => []];
        $orderShipment = $this->createPartialMock(
            Shipment::class,
            ['load', 'save', 'getStore', '__wakeup']
        );

        // Mock store with disabled config
        $store = $this->createMock(Store::class);
        $store->expects($this->once())
            ->method('getConfig')
            ->with('sales_email/shipment/enabled')
            ->willReturn(false);

        $orderShipment->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['order_id', null, $orderId],
                    ['shipment_id', null, $shipmentId],
                    ['shipment', null, $shipment],
                    ['tracking', null, $tracking]
                ]
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
            ->willReturn($orderShipment);

        // Verify notify is NOT called when disabled
        $this->objectManager->expects($this->never())
            ->method('create');

        $this->messageManager->expects($this->once())
            ->method('addWarningMessage')
            ->with('Shipment emails are disabled for this store. No email was sent.');

        $path = '*/*/view';
        $arguments = ['shipment_id' => $shipmentId];
        $this->prepareRedirect($path, $arguments);

        $this->shipmentEmail->execute();
        $this->assertEquals($this->response, $this->shipmentEmail->getResponse());
    }

    /**
     * @param string $path
     * @param array $arguments
     *
     * @return void
     */
    protected function prepareRedirect(string $path, array $arguments): void
    {
        $this->actionFlag->expects($this->any())
            ->method('get')
            ->with('', 'check_url_settings')
            ->willReturn(true);
        $this->session->expects($this->any())
            ->method('setIsUrlNotice')
            ->with(true);
        $this->resultRedirect
            ->method('setPath')
            ->with($path, ['shipment_id' => $arguments['shipment_id']]);
    }

    /**
     * Test LocalizedException is caught and proper error message is displayed
     *
     * @return void
     */
    public function testEmailWithLocalizedException(): void
    {
        $shipmentId = 1000012;
        $orderId = 10003;
        $tracking = [];
        $shipment = ['items' => []];
        $exceptionMessage = 'Localized exception message';

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['order_id', null, $orderId],
                    ['shipment_id', null, $shipmentId],
                    ['shipment', null, $shipment],
                    ['tracking', null, $tracking]
                ]
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

        // Simulate LocalizedException being thrown
        $this->shipmentLoader->expects($this->once())
            ->method('load')
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__($exceptionMessage)));

        // Verify the exception message is added as error
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with($exceptionMessage);

        $path = '*/*/view';
        $arguments = ['shipment_id' => $shipmentId];
        $this->prepareRedirect($path, $arguments);

        $this->shipmentEmail->execute();
    }

    /**
     * Test generic Exception is caught and generic error message is displayed
     *
     * @return void
     */
    public function testEmailWithGenericException(): void
    {
        $shipmentId = 1000012;
        $orderId = 10003;
        $tracking = [];
        $shipment = ['items' => []];

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['order_id', null, $orderId],
                    ['shipment_id', null, $shipmentId],
                    ['shipment', null, $shipment],
                    ['tracking', null, $tracking]
                ]
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

        // Simulate generic Exception being thrown
        $this->shipmentLoader->expects($this->once())
            ->method('load')
            ->willThrowException(new \Exception('Some error occurred'));

        // Verify generic error message is added
        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with('Cannot send shipment information.');

        $path = '*/*/view';
        $arguments = ['shipment_id' => $shipmentId];
        $this->prepareRedirect($path, $arguments);

        $this->shipmentEmail->execute();
    }
}
