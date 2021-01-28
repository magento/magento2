<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Paypal\Model\Ipn
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Sales\Model\Order;

class IpnTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Paypal\Model\Ipn
     */
    protected $_ipn;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_orderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_paypalInfo;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $configFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $curlFactory;

    protected function setUp(): void
    {
        $methods = [
            'create',
            'loadByIncrementId',
            'canFetchPaymentReviewUpdate',
            'getId',
            'getPayment',
            'getMethod',
            'setState',
            'getStoreId',
            'update',
            'getAdditionalInformation',
            'getEmailSent',
            'save',
            'getState',
        ];
        $this->_orderMock = $this->createPartialMock(\Magento\Sales\Model\OrderFactory::class, $methods);
        $this->_orderMock->expects($this->any())->method('create')->willReturnSelf();
        $this->_orderMock->expects($this->any())->method('loadByIncrementId')->willReturnSelf();
        $this->_orderMock->expects($this->any())->method('getId')->willReturnSelf();
        $this->_orderMock->expects($this->any())->method('getMethod')->willReturnSelf();
        $this->_orderMock->expects($this->any())->method('getStoreId')->willReturnSelf();
        $this->_orderMock->expects($this->any())->method('getEmailSent')->willReturn(true);

        $this->configFactory = $this->createPartialMock(\Magento\Paypal\Model\ConfigFactory::class, ['create']);
        $configMock = $this->getMockBuilder(\Magento\Paypal\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configFactory->expects($this->any())->method('create')->willReturn($configMock);
        $configMock->expects($this->any())->method('isMethodActive')->willReturn(true);
        $configMock->expects($this->any())->method('isMethodAvailable')->willReturn(true);
        $configMock->expects($this->any())->method('getValue')->willReturn(null);
        $configMock->expects($this->any())->method('getPayPalIpnUrl')
            ->willReturn('https://ipnpb_paypal_url');

        $this->curlFactory = $this->createPartialMock(
            \Magento\Framework\HTTP\Adapter\CurlFactory::class,
            ['create', 'setConfig', 'write', 'read']
        );
        $this->curlFactory->expects($this->any())->method('create')->willReturnSelf();
        $this->curlFactory->expects($this->any())->method('setConfig')->willReturnSelf();
        $this->curlFactory->expects($this->any())->method('write')->willReturnSelf();
        $this->curlFactory->expects($this->any())->method('read')->willReturn(
            '
                VERIFIED'
        );
        $this->_paypalInfo = $this->createPartialMock(
            \Magento\Paypal\Model\Info::class,
            ['importToPayment', 'getMethod', 'getAdditionalInformation']
        );
        $this->_paypalInfo->expects($this->any())->method('getMethod')->willReturn('some_method');
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_ipn = $objectHelper->getObject(
            \Magento\Paypal\Model\Ipn::class,
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
        $methods = [
            'setPreparedMessage',
            '__wakeup',
            'setTransactionId',
            'setParentTransactionId',
            'setIsTransactionClosed',
            'registerAuthorizationNotification',
        ];
        $payment = $this->createPartialMock(\Magento\Sales\Model\Order\Payment::class, $methods);
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
            \Magento\Sales\Model\Order\Payment::class,
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
        $this->_orderMock->method('getState')->willReturn(
            Order::STATE_PENDING_PAYMENT
        );
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
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_ipn = $objectHelper->getObject(
            \Magento\Paypal\Model\Ipn::class,
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
        /** @var \Magento\Sales\Model\Order\Payment $paymentMock */
        $paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->setMethods([
                'getAdditionalInformation',
                'setTransactionId',
                'setNotificationResult',
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

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_ipn = $objectHelper->getObject(
            \Magento\Paypal\Model\Ipn::class,
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
