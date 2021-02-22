<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Unit\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Sales\Model\ValidatorResultInterface;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface;
use Magento\Sales\Model\Order\Shipment\Validation\QuantityValidator;

/**
 * Class SaveTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $shipmentLoader;

    /**
     * @var \Magento\Shipping\Model\Shipping\LabelGenerator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $labelGenerator;

    /**
     * @var ShipmentSender|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $shipmentSender;

    /**
     * @var Action\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $actionFlag;

    /**
     * @var \Magento\Backend\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save
     */
    protected $saveAction;

    /**
     * @var ShipmentValidatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $shipmentValidatorMock;

    /**
     * @var ValidatorResultInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $validationResult;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->shipmentLoader = $this->getMockBuilder(
            \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['setShipmentId', 'setOrderId', 'setShipment', 'setTracking', 'load'])
            ->getMock();
        $this->validationResult = $this->getMockBuilder(ValidatorResultInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->labelGenerator = $this->getMockBuilder(\Magento\Shipping\Model\Shipping\LabelGenerator::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->shipmentSender = $this->getMockBuilder(\Magento\Sales\Model\Order\Email\Sender\ShipmentSender::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->context = $this->createPartialMock(\Magento\Backend\App\Action\Context::class, [
                'getRequest', 'getResponse', 'getMessageManager', 'getRedirect',
                'getObjectManager', 'getSession', 'getActionFlag', 'getHelper',
                'getResultRedirectFactory', 'getFormKeyValidator'
            ]);
        $this->response = $this->createPartialMock(
            \Magento\Framework\App\ResponseInterface::class,
            ['setRedirect', 'sendResponse']
        );
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()->getMock();
        $this->objectManager = $this->createPartialMock(
            \Magento\Framework\ObjectManager\ObjectManager::class,
            ['create', 'get']
        );
        $this->messageManager = $this->createPartialMock(
            \Magento\Framework\Message\Manager::class,
            ['addSuccessMessage', 'addErrorMessage']
        );
        $this->session = $this->createPartialMock(
            \Magento\Backend\Model\Session::class,
            ['setIsUrlNotice', 'getCommentText']
        );
        $this->actionFlag = $this->createPartialMock(\Magento\Framework\App\ActionFlag::class, ['get']);
        $this->helper = $this->createPartialMock(\Magento\Backend\Helper\Data::class, ['getUrl']);

        $this->resultRedirect = $this->createPartialMock(
            \Magento\Framework\Controller\Result\Redirect::class,
            ['setPath']
        );
        $this->resultRedirect->expects($this->any())
            ->method('setPath')
            ->willReturn($this->resultRedirect);

        $resultRedirectFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\RedirectFactory::class,
            ['create']
        );
        $resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $this->formKeyValidator = $this->createPartialMock(
            \Magento\Framework\Data\Form\FormKey\Validator::class,
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
            \Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save::class,
            [
                'labelGenerator' => $this->labelGenerator,
                'shipmentSender' => $this->shipmentSender,
                'context' => $this->context,
                'shipmentLoader' => $this->shipmentLoader,
                'request' => $this->request,
                'response' => $this->response,
                'shipmentValidator' => $this->shipmentValidatorMock
            ]
        );
    }

    /**
     * @param bool $formKeyIsValid
     * @param bool $isPost
     * @dataProvider executeDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute($formKeyIsValid, $isPost)
    {
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
            $shipmentData = ['items' => [], 'send_email' => ''];
            $shipment = $this->createPartialMock(
                \Magento\Sales\Model\Order\Shipment::class,
                ['load', 'save', 'register', 'getOrder', 'getOrderId', '__wakeup']
            );
            $order = $this->createPartialMock(\Magento\Sales\Model\Order::class, ['setCustomerNoteNotify', '__wakeup']);

            $this->request->expects($this->any())
                ->method('getParam')
                ->willReturnMap(
                    
                        [
                            ['order_id', null, $orderId],
                            ['shipment_id', null, $shipmentId],
                            ['shipment', null, $shipmentData],
                            ['tracking', null, $tracking],
                        ]
                    
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
                ->willReturn($shipment);
            $shipment->expects($this->once())
                ->method('register')
                ->willReturnSelf();
            $shipment->expects($this->any())
                ->method('getOrder')
                ->willReturn($order);
            $order->expects($this->once())
                ->method('setCustomerNoteNotify')
                ->with(false);
            $this->labelGenerator->expects($this->any())
                ->method('create')
                ->with($shipment, $this->request)
                ->willReturn(true);
            $saveTransaction = $this->getMockBuilder(\Magento\Framework\DB\Transaction::class)
                ->disableOriginalConstructor()
                ->setMethods([])
                ->getMock();
            $saveTransaction->expects($this->at(0))
                ->method('addObject')
                ->with($shipment)
                ->willReturnSelf();
            $saveTransaction->expects($this->at(1))
                ->method('addObject')
                ->with($order)
                ->willReturnSelf();
            $saveTransaction->expects($this->at(2))
                ->method('save');

            $this->session->expects($this->once())
                ->method('getCommentText')
                ->with(true);

            $this->objectManager->expects($this->once())
                ->method('create')
                ->with(\Magento\Framework\DB\Transaction::class)
                ->willReturn($saveTransaction);
            $this->objectManager->expects($this->once())
                ->method('get')
                ->with(\Magento\Backend\Model\Session::class)
                ->willReturn($this->session);
            $arguments = ['order_id' => $orderId];
            $shipment->expects($this->once())
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
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [false, false],
            [true, false],
            [false, true],
            [true, true]
        ];
    }

    /**
     * @param array $arguments
     */
    protected function prepareRedirect(array $arguments = [])
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
