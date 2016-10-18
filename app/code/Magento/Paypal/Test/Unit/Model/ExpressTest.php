<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Paypal\Model\Api\ProcessableException as ApiProcessableException;
use Magento\Paypal\Model\Express;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class ExpressTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExpressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $errorCodes = [
        ApiProcessableException::API_INTERNAL_ERROR,
        ApiProcessableException::API_UNABLE_PROCESS_PAYMENT_ERROR_CODE,
        ApiProcessableException::API_DO_EXPRESS_CHECKOUT_FAIL,
        ApiProcessableException::API_UNABLE_TRANSACTION_COMPLETE,
        ApiProcessableException::API_TRANSACTION_EXPIRED,
        ApiProcessableException::API_MAX_PAYMENT_ATTEMPTS_EXCEEDED,
        ApiProcessableException::API_COUNTRY_FILTER_DECLINE,
        ApiProcessableException::API_MAXIMUM_AMOUNT_FILTER_DECLINE,
        ApiProcessableException::API_OTHER_FILTER_DECLINE,
        ApiProcessableException::API_ADDRESS_MATCH_FAIL
    ];

    /**
     * @var Express
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_checkoutSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_pro;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_nvp;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionBuilder;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    protected function setUp()
    {
        $this->_checkoutSession = $this->getMock(
            \Magento\Checkout\Model\Session::class,
            ['getPaypalTransactionData', 'setPaypalTransactionData'],
            [],
            '',
            false
        );
        $this->transactionBuilder = $this->getMockForAbstractClass(
            \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface::class,
            [],
            '',
            false,
            false
        );
        $this->_nvp = $this->getMock(
            \Magento\Paypal\Model\Api\Nvp::class,
            ['setProcessableErrors', 'setAmount', 'setCurrencyCode', 'setTransactionId', 'callDoAuthorization'],
            [],
            '',
            false
        );
        $this->_pro = $this->getMock(
            \Magento\Paypal\Model\Pro::class,
            ['setMethod', 'getApi', 'importPaymentInfo', 'resetApi'],
            [],
            '',
            false
        );
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->setMethods(['dispatch'])
            ->getMockForAbstractClass();

        $this->_pro->expects($this->any())->method('getApi')->will($this->returnValue($this->_nvp));
        $this->_helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testSetApiProcessableErrors()
    {
        $this->_nvp->expects($this->once())->method('setProcessableErrors')->with($this->errorCodes);

        $this->_model = $this->_helper->getObject(
            \Magento\Paypal\Model\Express::class,
            [
                'data' => [$this->_pro],
                'checkoutSession' => $this->_checkoutSession,
                'transactionBuilder' => $this->transactionBuilder
            ]
        );
    }

    public function testOrder()
    {
        $this->_nvp->expects($this->any())->method('setProcessableErrors')->will($this->returnSelf());
        $this->_nvp->expects($this->any())->method('setAmount')->will($this->returnSelf());
        $this->_nvp->expects($this->any())->method('setCurrencyCode')->will($this->returnSelf());
        $this->_nvp->expects($this->any())->method('setTransactionId')->will($this->returnSelf());
        $this->_nvp->expects($this->any())->method('callDoAuthorization')->will($this->returnSelf());

        $this->_checkoutSession->expects($this->once())->method('getPaypalTransactionData')->will(
            $this->returnValue([])
        );
        $this->_checkoutSession->expects($this->once())->method('setPaypalTransactionData')->with([]);

        $currency = $this->getMock(\Magento\Directory\Model\Currency::class, ['__wakeup', 'formatTxt'], [], '', false);
        $paymentModel = $this->getMock(
            \Magento\Sales\Model\Order\Payment::class,
            [
                '__wakeup',
                'getBaseCurrency',
                'getOrder',
                'getIsTransactionPending',
                'addStatusHistoryComment',
                'addTransactionCommentsToOrder'
            ],
            [],
            '',
            false
        );
        $order = $this->getMock(
            \Magento\Sales\Model\Order::class,
            ['setState', 'getBaseCurrency', 'getBaseCurrencyCode', 'setStatus'],
            [],
            '',
            false
        );
        $paymentModel->expects($this->any())->method('getOrder')->willReturn($order);
        $order->expects($this->any())->method('getBaseCurrency')->willReturn($currency);
        $order->expects($this->any())->method('setState')->with('payment_review')->willReturnSelf();
        $paymentModel->expects($this->any())->method('getIsTransactionPending')->will($this->returnSelf());
        $this->transactionBuilder->expects($this->any())->method('setOrder')->with($order)->will($this->returnSelf());
        $this->transactionBuilder->expects($this->any())->method('setPayment')->will($this->returnSelf());
        $this->transactionBuilder->expects($this->any())->method('setTransactionId')->will($this->returnSelf());
        $this->_model = $this->_helper->getObject(
            \Magento\Paypal\Model\Express::class,
            [
                'data' => [$this->_pro],
                'checkoutSession' => $this->_checkoutSession,
                'transactionBuilder' => $this->transactionBuilder
            ]
        );
        $this->assertEquals($this->_model, $this->_model->order($paymentModel, 12.3));
    }

    public function testAssignData()
    {
        $transportValue = 'something';

        $data = new DataObject(
            [
                PaymentInterface::KEY_ADDITIONAL_DATA => [
                    Express\Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT => $transportValue,
                    Express\Checkout::PAYMENT_INFO_TRANSPORT_PAYER_ID => $transportValue,
                    Express\Checkout::PAYMENT_INFO_TRANSPORT_TOKEN => $transportValue
                ]
            ]
        );

        $this->_model = $this->_helper->getObject(
            \Magento\Paypal\Model\Express::class,
            [
                'data' => [$this->_pro],
                'checkoutSession' => $this->_checkoutSession,
                'transactionBuilder' => $this->transactionBuilder,
                'eventDispatcher' => $this->eventManagerMock,
            ]
        );

        $paymentInfo = $this->getMock(InfoInterface::class);
        $this->_model->setInfoInstance($paymentInfo);

        $this->parentAssignDataExpectation($data);

        $paymentInfo->expects(static::exactly(3))
            ->method('setAdditionalInformation')
            ->withConsecutive(
                [Express\Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT, $transportValue],
                [Express\Checkout::PAYMENT_INFO_TRANSPORT_PAYER_ID, $transportValue],
                [Express\Checkout::PAYMENT_INFO_TRANSPORT_TOKEN, $transportValue]
            );

        $this->_model->assignData($data);
    }

    /**
     * @param DataObject $data
     */
    private function parentAssignDataExpectation(DataObject $data)
    {
        $eventData = [
            AbstractDataAssignObserver::METHOD_CODE => $this,
            AbstractDataAssignObserver::MODEL_CODE => $this->_model->getInfoInstance(),
            AbstractDataAssignObserver::DATA_CODE => $data
        ];

        $this->eventManagerMock->expects(static::exactly(2))
            ->method('dispatch')
            ->willReturnMap(
                [
                    [
                        'payment_method_assign_data_' . $this->_model->getCode(),
                        $eventData
                    ],
                    [
                        'payment_method_assign_data',
                        $eventData
                    ]
                ]
            );
    }
}
