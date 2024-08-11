<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Paypal\Model\Ipn
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Paypal\Model\Info;
use Magento\Paypal\Model\Ipn;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\OrderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IpnTest extends TestCase
{
    /**
     * @var Ipn
     */
    protected $_ipn;

    /**
     * @var MockObject
     */
    protected $_orderMock;

    /**
     * @var MockObject
     */
    protected $_paypalInfo;

    /**
     * @var MockObject
     */
    protected $configFactory;

    /**
     * @var MockObject
     */
    protected $curlFactory;

    protected function setUp(): void
    {
        $this->_orderMock = $this->getMockBuilder(OrderFactory::class)
            ->addMethods(
                [
                    'loadByIncrementId',
                    'canFetchPaymentReviewUpdate',
                    'getId',
                    'getPayment',
                    'getMethod',
                    'getStoreId',
                    'update',
                    'getAdditionalInformation',
                    'getEmailSent',
                    'save',
                    'getState',
                    'setState'
                ]
            )
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_orderMock->expects($this->any())->method('create')->willReturnSelf();
        $this->_orderMock->expects($this->any())->method('loadByIncrementId')->willReturnSelf();
        $this->_orderMock->expects($this->any())->method('getId')->willReturnSelf();
        $this->_orderMock->expects($this->any())->method('getMethod')->willReturnSelf();
        $this->_orderMock->expects($this->any())->method('getStoreId')->willReturnSelf();
        $this->_orderMock->expects($this->any())->method('getEmailSent')->willReturn(true);

        $this->configFactory = $this->createPartialMock(ConfigFactory::class, ['create']);
        $configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configFactory->expects($this->any())->method('create')->willReturn($configMock);
        $configMock->expects($this->any())->method('isMethodActive')->willReturn(true);
        $configMock->expects($this->any())->method('isMethodAvailable')->willReturn(true);
        $configMock->expects($this->any())->method('getValue')->willReturn(null);
        $configMock->expects($this->any())->method('getPayPalIpnUrl')
            ->willReturn('https://ipnpb_paypal_url');

        $this->curlFactory = $this->getMockBuilder(CurlFactory::class)
            ->addMethods(['setOptions', 'write', 'read'])
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->curlFactory->expects($this->any())->method('create')->willReturnSelf();
        $this->curlFactory->expects($this->any())->method('setOptions')->willReturnSelf();
        $this->curlFactory->expects($this->any())->method('write')->willReturnSelf();
        $this->curlFactory->expects($this->any())->method('read')->willReturn(
            '
                VERIFIED'
        );
        $this->_paypalInfo = $this->getMockBuilder(Info::class)
            ->addMethods(['getMethod', 'getAdditionalInformation'])
            ->onlyMethods(['importToPayment'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_paypalInfo->expects($this->any())->method('getMethod')->willReturn('some_method');
        $objectHelper = new ObjectManager($this);
        $this->_ipn = $objectHelper->getObject(
            Ipn::class,
            [
                'configFactory' => $this->configFactory,
                'curlFactory' => $this->curlFactory,
                'orderFactory' => $this->_orderMock,
                'paypalInfo' => $this->_paypalInfo,
                'data' => ['payment_status' => 'Pending', 'pending_reason' => 'authorization']
            ]
        );
    }

    public function testLegacyRegisterPaymentAuthorization()
    {
        $this->_orderMock->expects($this->any())->method('canFetchPaymentReviewUpdate')->willReturn(
            false
        );
        $payment = $this->getMockBuilder(Payment::class)
            ->addMethods(['setPreparedMessage'])
            ->onlyMethods(
                [
                    '__wakeup',
                    'setTransactionId',
                    'setParentTransactionId',
                    'setIsTransactionClosed',
                    'registerAuthorizationNotification'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $payment->expects($this->any())->method('setPreparedMessage')->willReturnSelf();
        $payment->expects($this->any())->method('setTransactionId')->willReturnSelf();
        $payment->expects($this->any())->method('setParentTransactionId')->willReturnSelf();
        $payment->expects($this->any())->method('setIsTransactionClosed')->willReturnSelf();
        $this->_orderMock->expects($this->any())->method('getPayment')->willReturn($payment);
        $this->_orderMock->expects($this->any())->method('getAdditionalInformation')->willReturn([]);

        $this->_paypalInfo->expects($this->once())->method('importToPayment');
        $this->_ipn->processIpnRequest();
    }

    public function testPaymentReviewRegisterPaymentAuthorization()
    {
        $this->_orderMock->expects($this->any())->method('getPayment')->willReturnSelf();
        $this->_orderMock->expects($this->any())->method('canFetchPaymentReviewUpdate')->willReturn(true);
        $this->_orderMock->expects($this->once())->method('update')->with(true)->willReturnSelf();
        $this->_ipn->processIpnRequest();
    }

    public function testPaymentReviewRegisterPaymentFraud()
    {
        $paymentMock = $this->createPartialMock(
            Payment::class,
            ['getAdditionalInformation', '__wakeup', 'registerCaptureNotification']
        );
        $paymentMock->expects($this->any())
            ->method('getAdditionalInformation')
            ->willReturn([]);
        $paymentMock->expects($this->any())
            ->method('registerCaptureNotification')
            ->willReturn(true);
        $this->_orderMock->expects($this->any())->method('getPayment')->willReturn($paymentMock);
        $this->_orderMock->expects($this->any())->method('canFetchPaymentReviewUpdate')->willReturn(true);
        $this->_orderMock->expects($this->any())->method('getState')->willReturn(
            Order::STATE_PENDING_PAYMENT
        );
        $this->_orderMock->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_PROCESSING);
        $this->_paypalInfo->expects($this->once())
            ->method('importToPayment')
            ->with(
                [
                    'payment_status' => 'pending',
                    'pending_reason' => 'fraud',
                    'collected_fraud_filters' => ['Maximum Transaction Amount'],
                ],
                $paymentMock
            );
        $objectHelper = new ObjectManager($this);
        $this->_ipn = $objectHelper->getObject(
            Ipn::class,
            [
                'configFactory' => $this->configFactory,
                'curlFactory' => $this->curlFactory,
                'orderFactory' => $this->_orderMock,
                'paypalInfo' => $this->_paypalInfo,
                'data' => [
                    'payment_status' => 'Pending',
                    'pending_reason' => 'fraud',
                    'fraud_management_pending_filters_1' => 'Maximum Transaction Amount',
                ]
            ]
        );
        $this->_ipn->processIpnRequest();
        $this->assertEquals('IPN "Pending"', $paymentMock->getPreparedMessage());
    }

    public function testRegisterPaymentDenial()
    {
        /** @var Payment $paymentMock */
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->addMethods(['setNotificationResult',])
            ->onlyMethods([
                'getAdditionalInformation',
                'setTransactionId',
                'setIsTransactionClosed',
                'deny'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock->expects($this->exactly(6))->method('getAdditionalInformation')->willReturn([]);
        $paymentMock->expects($this->once())->method('setTransactionId')->willReturnSelf();
        $paymentMock->expects($this->once())->method('setNotificationResult')->willReturnSelf();
        $paymentMock->expects($this->once())->method('setIsTransactionClosed')->willReturnSelf();
        $paymentMock->expects($this->once())->method('deny')->with(false)->willReturnSelf();

        $this->_orderMock->expects($this->exactly(4))->method('getPayment')->willReturn($paymentMock);

        $this->_paypalInfo->expects($this->once())
            ->method('importToPayment')
            ->with(['payment_status' => 'denied'], $paymentMock);

        $objectHelper = new ObjectManager($this);
        $this->_ipn = $objectHelper->getObject(
            Ipn::class,
            [
                'configFactory' => $this->configFactory,
                'curlFactory' => $this->curlFactory,
                'orderFactory' => $this->_orderMock,
                'paypalInfo' => $this->_paypalInfo,
                'data' => [
                    'payment_status' => 'Denied',
                ]
            ]
        );

        $this->_ipn->processIpnRequest();
    }
}
