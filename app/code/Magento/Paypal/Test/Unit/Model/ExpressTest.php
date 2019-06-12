<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Checkout\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Paypal\Model\Api\Nvp;
<<<<<<< HEAD
=======
use Magento\Paypal\Model\Api\ProcessableException;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
use Magento\Paypal\Model\Api\ProcessableException as ApiProcessableException;
use Magento\Paypal\Model\Express;
use Magento\Paypal\Model\Pro;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
<<<<<<< HEAD
use \PHPUnit_Framework_MockObject_MockObject as MockObject;
=======
use PHPUnit_Framework_MockObject_MockObject as MockObject;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

/**
 * Class ExpressTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExpressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private static $authorizationExpiredCode = 10601;

    /**
     * @var array
     */
    private $errorCodes = [
        ApiProcessableException::API_INTERNAL_ERROR,
        ApiProcessableException::API_UNABLE_PROCESS_PAYMENT_ERROR_CODE,
        ApiProcessableException::API_DO_EXPRESS_CHECKOUT_FAIL,
        ApiProcessableException::API_UNABLE_TRANSACTION_COMPLETE,
        ApiProcessableException::API_TRANSACTION_EXPIRED,
        ApiProcessableException::API_MAX_PAYMENT_ATTEMPTS_EXCEEDED,
        ApiProcessableException::API_COUNTRY_FILTER_DECLINE,
        ApiProcessableException::API_MAXIMUM_AMOUNT_FILTER_DECLINE,
        ApiProcessableException::API_OTHER_FILTER_DECLINE,
        ApiProcessableException::API_ADDRESS_MATCH_FAIL,
    ];

    /**
     * @var Express
     */
    private $model;

    /**
     * @var Session|MockObject
     */
    private $checkoutSession;

    /**
     * @var Pro|MockObject
     */
    private $pro;

    /**
     * @var Nvp|MockObject
     */
    private $nvp;

    /**
     * @var ObjectManager
     */
    private $helper;

    /**
     * @var BuilderInterface|MockObject
     */
    private $transactionBuilder;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManager;

    protected function setUp()
    {
<<<<<<< HEAD
=======
        $this->errorCodes[] = self::$authorizationExpiredCode;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $this->checkoutSession = $this->createPartialMock(
            Session::class,
            ['getPaypalTransactionData', 'setPaypalTransactionData']
        );
        $this->transactionBuilder = $this->getMockForAbstractClass(
            BuilderInterface::class,
            [],
            '',
            false,
            false
        );
        $this->nvp = $this->createPartialMock(
            Nvp::class,
            [
                'setProcessableErrors',
                'setAmount',
                'setCurrencyCode',
                'setTransactionId',
                'callDoAuthorization',
<<<<<<< HEAD
                'setData'
=======
                'setData',
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ]
        );
        $this->pro = $this->createPartialMock(
            Pro::class,
<<<<<<< HEAD
            ['setMethod', 'getApi', 'importPaymentInfo', 'resetApi']
=======
            ['setMethod', 'getApi', 'importPaymentInfo', 'resetApi', 'void']
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        );
        $this->eventManager = $this->getMockBuilder(ManagerInterface::class)
            ->setMethods(['dispatch'])
            ->getMockForAbstractClass();

<<<<<<< HEAD
        $this->pro->expects($this->any())->method('getApi')->will($this->returnValue($this->nvp));
=======
        $this->pro->method('getApi')
            ->willReturn($this->nvp);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $this->helper = new ObjectManager($this);
    }

    /**
     * Tests setting the list of processable errors.
     */
    public function testSetApiProcessableErrors()
    {
        $this->nvp->expects($this->once())->method('setProcessableErrors')->with($this->errorCodes);

        $this->model = $this->helper->getObject(
            \Magento\Paypal\Model\Express::class,
            [
                'data' => [$this->pro],
                'checkoutSession' => $this->checkoutSession,
<<<<<<< HEAD
                'transactionBuilder' => $this->transactionBuilder
=======
                'transactionBuilder' => $this->transactionBuilder,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ]
        );
    }

    /**
<<<<<<< HEAD
     * Tests order payment action.
=======
     * Tests canceling order payment when expired authorization generates exception on a client.
     */
    public function testCancelWithExpiredAuthorizationTransaction()
    {
        $this->pro->method('void')
            ->willThrowException(
                new ProcessableException(__('PayPal gateway has rejected request.'), null, 10601)
            );

        $this->model = $this->helper->getObject(Express::class, ['data' => [$this->pro]]);
        /** @var Payment|MockObject $paymentModel */
        $paymentModel = $this->createMock(Payment::class);
        $paymentModel->expects($this->once())
            ->method('setTransactionId')
            ->with(null);
        $paymentModel->expects($this->once())
            ->method('setIsTransactionClosed')
            ->with(true);
        $paymentModel->expects($this->once())
            ->method('setShouldCloseParentTransaction')
            ->with(true);

        $this->model->cancel($paymentModel);
    }

    /**
     * Tests order payment action.
     *
     * @return void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function testOrder()
    {
        $transactionData = ['TOKEN' => 'EC-7NJ4634216284232D'];
        $this->checkoutSession
            ->method('getPaypalTransactionData')
            ->willReturn($transactionData);

        $order = $this->createPartialMock(Order::class, ['setActionFlag']);
        $order->method('setActionFlag')
            ->with(Order::ACTION_FLAG_INVOICE, false)
            ->willReturnSelf();

        $paymentModel = $this->createPartialMock(Payment::class, ['getOrder']);
        $paymentModel->method('getOrder')
            ->willReturn($order);

        $this->model = $this->helper->getObject(
            \Magento\Paypal\Model\Express::class,
            [
                'data' => [$this->pro],
<<<<<<< HEAD
                'checkoutSession' => $this->checkoutSession
=======
                'checkoutSession' => $this->checkoutSession,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ]
        );

        $this->nvp->method('setData')
            ->with($transactionData)
            ->willReturnSelf();

        static::assertEquals($this->model, $this->model->order($paymentModel, 12.3));
    }

    /**
     * Tests data assigning.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testAssignData()
    {
        $transportValue = 'something';

        $extensionAttribute = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\PaymentExtensionInterface::class,
            [],
            '',
            false,
            false
        );

        $data = new DataObject(
            [
                PaymentInterface::KEY_ADDITIONAL_DATA => [
                    Express\Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT => $transportValue,
                    Express\Checkout::PAYMENT_INFO_TRANSPORT_PAYER_ID => $transportValue,
                    Express\Checkout::PAYMENT_INFO_TRANSPORT_TOKEN => $transportValue,
                    \Magento\Framework\Api\ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY => $extensionAttribute
                ]
            ]
        );

        $this->model = $this->helper->getObject(
            \Magento\Paypal\Model\Express::class,
            [
                'data' => [$this->pro],
                'checkoutSession' => $this->checkoutSession,
                'transactionBuilder' => $this->transactionBuilder,
                'eventDispatcher' => $this->eventManager,
            ]
        );

        $paymentInfo = $this->createMock(InfoInterface::class);
        $this->model->setInfoInstance($paymentInfo);

        $this->parentAssignDataExpectation($data);

        $paymentInfo->expects(static::exactly(3))
            ->method('setAdditionalInformation')
            ->withConsecutive(
                [Express\Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT, $transportValue],
                [Express\Checkout::PAYMENT_INFO_TRANSPORT_PAYER_ID, $transportValue],
                [Express\Checkout::PAYMENT_INFO_TRANSPORT_TOKEN, $transportValue]
            );

        $this->model->assignData($data);
    }

    /**
     * @param DataObject $data
     */
    private function parentAssignDataExpectation(DataObject $data)
    {
        $eventData = [
            AbstractDataAssignObserver::METHOD_CODE => $this,
            AbstractDataAssignObserver::MODEL_CODE => $this->model->getInfoInstance(),
            AbstractDataAssignObserver::DATA_CODE => $data
        ];

        $this->eventManager->expects(static::exactly(2))
            ->method('dispatch')
            ->willReturnMap(
                [
                    [
                        'payment_method_assign_data_' . $this->model->getCode(),
<<<<<<< HEAD
                        $eventData
=======
                        $eventData,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                    ],
                    [
                        'payment_method_assign_data',
                        $eventData,
                    ]
                ]
            );
    }
}
