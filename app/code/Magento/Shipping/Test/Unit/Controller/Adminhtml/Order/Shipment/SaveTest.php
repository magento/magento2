<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Message\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Helper\Data as SalesData;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface;
use Magento\Sales\Model\Order\Shipment\Validation\QuantityValidator;
use Magento\Sales\Model\ValidatorResultInterface;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class SaveTest extends TestCase
{
    /**
     * @var ShipmentLoader|MockObject
     */
    protected $shipmentLoader;

    /**
     * @var LabelGenerator|MockObject
     */
    protected $labelGenerator;

    /**
     * @var ShipmentSender|MockObject
     */
    protected $shipmentSender;

    /**
     * @var Action\Context|MockObject
     */
    protected $context;

    /**
     * @var Http|MockObject
     */
    protected $request;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $response;

    /**
     * @var Manager|MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|MockObject
     */
    protected $objectManager;

    /**
     * @var Session|MockObject
     */
    protected $session;

    /**
     * @var ActionFlag|MockObject
     */
    protected $actionFlag;

    /**
     * @var Data|MockObject
     */
    protected $helper;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirect;

    /**
     * @var Validator|MockObject
     */
    protected $formKeyValidator;

    /**
     * @var Save
     */
    protected $saveAction;

    /**
     * @var ShipmentValidatorInterface|MockObject
     */
    private $shipmentValidatorMock;

    /**
     * @var ValidatorResultInterface|MockObject
     */
    private $validationResult;

    /**
     * @var SalesData|MockObject
     */
    private $salesData;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->shipmentLoader = $this->getMockBuilder(ShipmentLoader::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load'])
            ->addMethods(['setShipmentId', 'setOrderId', 'setShipment', 'setTracking'])
            ->getMock();
        $this->validationResult = $this->getMockBuilder(ValidatorResultInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->labelGenerator = $this->getMockBuilder(LabelGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shipmentSender = $this->getMockBuilder(ShipmentSender::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['send'])
            ->getMock();
        $this->shipmentSender->expects($this->any())
            ->method('send')
            ->willReturn(true);
        $this->salesData = $this->getMockBuilder(SalesData::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['canSendNewShipmentEmail'])
            ->getMock();
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->context = $this->createPartialMock(Context::class, [
            'getRequest', 'getResponse', 'getMessageManager', 'getRedirect',
            'getObjectManager', 'getSession', 'getActionFlag', 'getHelper',
            'getResultRedirectFactory', 'getFormKeyValidator'
        ]);
        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->addMethods(['setRedirect'])
            ->onlyMethods(['sendResponse'])
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager = $this->createPartialMock(
            \Magento\Framework\ObjectManager\ObjectManager::class,
            ['create', 'get']
        );
        $this->messageManager = $this->createPartialMock(
            Manager::class,
            ['addSuccessMessage', 'addErrorMessage']
        );
        $this->session = $this->getMockBuilder(Session::class)
            ->addMethods(['setIsUrlNotice', 'getCommentText'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionFlag = $this->createPartialMock(ActionFlag::class, ['get']);
        $this->helper = $this->createPartialMock(Data::class, ['getUrl']);

        $this->resultRedirect = $this->createPartialMock(
            Redirect::class,
            ['setPath']
        );
        $this->resultRedirect->expects($this->any())
            ->method('setPath')
            ->willReturn($this->resultRedirect);

        $resultRedirectFactory = $this->createPartialMock(
            RedirectFactory::class,
            ['create']
        );
        $resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $this->formKeyValidator = $this->createPartialMock(
            Validator::class,
            ['validate']
        );

        $this->context->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
        $this->context->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);
        $this->context->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManager);
        $this->context->expects($this->once())
            ->method('getSession')
            ->willReturn($this->session);
        $this->context->expects($this->once())
            ->method('getActionFlag')
            ->willReturn($this->actionFlag);
        $this->context->expects($this->once())
            ->method('getHelper')
            ->willReturn($this->helper);
        $this->context->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($resultRedirectFactory);
        $this->context->expects($this->once())
            ->method('getFormKeyValidator')
            ->willReturn($this->formKeyValidator);

        $this->shipmentValidatorMock = $this->getMockBuilder(ShipmentValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->saveAction = $objectManagerHelper->getObject(
            Save::class,
            [
                'labelGenerator' => $this->labelGenerator,
                'shipmentSender' => $this->shipmentSender,
                'context' => $this->context,
                'shipmentLoader' => $this->shipmentLoader,
                'request' => $this->request,
                'response' => $this->response,
                'shipmentValidator' => $this->shipmentValidatorMock,
                'salesData' => $this->salesData
            ]
        );
    }

    /**
     * @param bool $formKeyIsValid
     * @param bool $isPost
     * @param string $sendEmail
     * @param bool $emailEnabled
     * @param bool $shouldEmailBeSent
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @dataProvider executeDataProvider
     */
    public function testExecute(
        $formKeyIsValid,
        $isPost,
        $sendEmail,
        $emailEnabled,
        $shouldEmailBeSent
    ): void {
        $this->formKeyValidator->expects($this->any())
            ->method('validate')
            ->willReturn($formKeyIsValid);

        $this->request->expects($this->any())
            ->method('isPost')
            ->willReturn($isPost);

        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->expects($this->once())
                ->method('addErrorMessage');

            $this->resultRedirect->expects($this->once())
                ->method('setPath')
                ->with('sales/order/index');

            $this->shipmentLoader->expects($this->never())
                ->method('load');

            $this->assertEquals($this->resultRedirect, $this->saveAction->execute());
        } else {
            $shipmentId = 1000012;
            $orderId = 10003;
            $tracking = [];
            $shipmentData = ['items' => [], 'send_email' => $sendEmail];
            $shipment = $this->createPartialMock(
                Shipment::class,
                ['load', 'save', 'register', 'getOrder', 'getOrderId', '__wakeup']
            );
            $order = $this->createPartialMock(Order::class, ['setCustomerNoteNotify', '__wakeup']);

            $this->request->expects($this->any())
                ->method('getParam')
                ->willReturnMap(
                    [
                        ['order_id', null, $orderId],
                        ['shipment_id', null, $shipmentId],
                        ['shipment', null, $shipmentData],
                        ['tracking', null, $tracking]
                    ]
                );

            $this->salesData->expects($this->any())
                ->method('canSendNewShipmentEmail')
                ->willReturn($emailEnabled);
            if ($shouldEmailBeSent) {
                $this->shipmentSender->expects($this->once())
                    ->method('send');
            }
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
                ->willReturn($shipment);
            $shipment->expects($this->once())
                ->method('register')->willReturnSelf();
            $shipment->expects($this->any())
                ->method('getOrder')
                ->willReturn($order);
            $order->expects($this->once())
                ->method('setCustomerNoteNotify')
                ->with(!empty($sendEmail));
            $this->labelGenerator->expects($this->any())
                ->method('create')
                ->with($shipment, $this->request)
                ->willReturn(true);
            $saveTransaction = $this->getMockBuilder(Transaction::class)
                ->disableOriginalConstructor()
                ->getMock();
            $saveTransaction
                ->method('addObject')
                ->willReturnCallback(fn($param) => match ([$param]) {
                    [$shipment] => $saveTransaction,
                    [$order] => $saveTransaction
                });

            $this->session->expects($this->once())
                ->method('getCommentText')
                ->with(true);

            $this->objectManager->expects($this->once())
                ->method('create')
                ->with(Transaction::class)
                ->willReturn($saveTransaction);
            $this->objectManager->expects($this->once())
                ->method('get')
                ->with(Session::class)
                ->willReturn($this->session);
            $arguments = ['order_id' => $orderId];
            $shipment->expects($this->any())
                ->method('getOrderId')
                ->willReturn($orderId);
            $this->prepareRedirect($arguments);

            $this->shipmentValidatorMock->expects($this->once())
                ->method('validate')
                ->with($shipment, [QuantityValidator::class])
                ->willReturn($this->validationResult);

            $this->validationResult->expects($this->once())
                ->method('hasMessages')
                ->willReturn(false);

            $this->saveAction->execute();
            $this->assertEquals($this->response, $this->saveAction->getResponse());
        }
    }

    /**
     * Test that canSendNewShipmentEmail is called with correct store ID
     *
     * @dataProvider storeIdDataProvider
     */
    public function testCanSendNewShipmentEmailWithStoreId(
        int $storeId,
        bool $sendEmailRequested,
        bool $emailEnabledForStore,
        bool $shouldSendEmail
    ): void {
        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->willReturn(true);

        $this->request->expects($this->once())
            ->method('isPost')
            ->willReturn(true);

        $shipmentId = 1000012;
        $orderId = 10003;
        $shipmentData = ['items' => [], 'send_email' => $sendEmailRequested ? 'on' : ''];

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['order_id', null, $orderId],
                ['shipment_id', null, $shipmentId],
                ['shipment', null, $shipmentData],
                ['tracking', null, []]
            ]);

        $order = $this->createPartialMock(Order::class, ['setCustomerNoteNotify', 'getStoreId', '__wakeup']);
        $order->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);

        $shipment = $this->createPartialMock(
            Shipment::class,
            ['load', 'save', 'register', 'getOrder', 'getOrderId', '__wakeup']
        );
        $shipment->expects($this->any())
            ->method('getOrder')
            ->willReturn($order);
        $shipment->expects($this->any())
            ->method('getOrderId')
            ->willReturn($orderId);

        if ($sendEmailRequested) {
            $this->salesData->expects($this->once())
                ->method('canSendNewShipmentEmail')
                ->with($storeId)
                ->willReturn($emailEnabledForStore);
        } else {
            $this->salesData->expects($this->never())
                ->method('canSendNewShipmentEmail');
        }

        if ($shouldSendEmail) {
            $this->shipmentSender->expects($this->once())
                ->method('send')
                ->with($shipment);
        } else {
            $this->shipmentSender->expects($this->never())
                ->method('send');
        }

        $this->shipmentLoader->expects($this->once())
            ->method('load')
            ->willReturn($shipment);

        $this->setupCommonMocks($shipment, $order, $orderId);

        $this->saveAction->execute();
    }

    /**
     * Test that email is not sent when disabled for specific store but enabled globally
     */
    public function testEmailNotSentWhenDisabledForSpecificStore(): void
    {
        $storeId = 2;
        $this->testCanSendNewShipmentEmailWithStoreId(
            $storeId,
            true,
            false,
            false
        );
    }

    /**
     * Test that email is sent when enabled for specific store even if disabled globally
     */
    public function testEmailSentWhenEnabledForSpecificStore(): void
    {
        $storeId = 2;
        $this->testCanSendNewShipmentEmailWithStoreId(
            $storeId,
            true,
            true,
            true
        );
    }

    /**
     * @return array
     */
    public static function storeIdDataProvider(): array
    {
        return [
            'default_store_email_requested_enabled' => [1, true, true, true],
            'default_store_email_requested_disabled' => [1, true, false, false],
            'custom_store_email_requested_enabled' => [2, true, true, true],
            'custom_store_email_requested_disabled' => [2, true, false, false],
            'custom_store_email_not_requested' => [2, false, true, false],
            'multistore_environment_store_3' => [3, true, true, true],
            'multistore_environment_store_5_disabled' => [5, true, false, false],
        ];
    }

    /**
     * @return array
     */
    public static function executeDataProvider(): array
    {
        /**
         * bool $formKeyIsValid
         * bool $isPost
         * string $sendEmail
         * bool $emailEnabled
         * bool $shouldEmailBeSent
         */
        return [
            [false, false, '', false, false],
            [true, false, '', false, false],
            [false, true, '', false, false],
            [true, true, '', false, false],
            [true, true, '', true, false],
            [true, true, 'on', false, false],
            [true, true, 'on', true, true],

        ];
    }

    /**
     * Setup common mocks needed for successful execution
     */
    private function setupCommonMocks(MockObject $shipment, MockObject $order, int $orderId): void
    {
        $shipment->expects($this->once())
            ->method('register')
            ->willReturnSelf();

        $order->expects($this->once())
            ->method('setCustomerNoteNotify');

        $this->labelGenerator->expects($this->any())
            ->method('create')
            ->willReturn(true);

        $saveTransaction = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $saveTransaction->method('addObject')->willReturnSelf();

        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(Transaction::class)
            ->willReturn($saveTransaction);

        $this->objectManager->expects($this->once())
            ->method('get')
            ->with(Session::class)
            ->willReturn($this->session);

        $this->session->expects($this->once())
            ->method('getCommentText')
            ->with(true);

        $this->shipmentValidatorMock->expects($this->once())
            ->method('validate')
            ->willReturn($this->validationResult);

        $this->validationResult->expects($this->once())
            ->method('hasMessages')
            ->willReturn(false);

        $this->prepareRedirect(['order_id' => $orderId]);
    }

    /**
     * @param array $arguments
     *
     * @return void
     */
    protected function prepareRedirect(array $arguments = []): void
    {
        $this->actionFlag->expects($this->any())
            ->method('get')
            ->with('', 'check_url_settings')
            ->willReturn(true);
        $this->session->expects($this->any())
            ->method('setIsUrlNotice')
            ->with(true);
        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('sales/order/view', $arguments);
    }
}
