<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Adminhtml;

use Magento\Payment\Model\MethodInterface;
use Magento\Paypal\Model\Adminhtml\Express;
use Magento\Paypal\Model\Api\Nvp;
use Magento\Paypal\Model\Pro;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use PHPUnit\Framework\TestCase;

/**
 * Test ability to make an authorization calls to Paypal API from admin.
 */
class ExpressTest extends TestCase
{
    /**
     * @var Express
     */
    private $express;

    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $payment;

    /**
     * @var MethodInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentInstance;

    /**
     * @var Pro|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pro;

    /**
     * @var Nvp|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nvp;

    /**
     * @var Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $order;

    /**
     * @var TransactionRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transactionRepository;

    /**
     * @var TransactionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transaction;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->nvp = $this->createPartialMock(
            Nvp::class,
            ['getData','setProcessableErrors', 'callDoAuthorization']
        );
        $this->nvp->method('getData')->willReturn([]);
        $this->nvp->method('setProcessableErrors')->willReturnSelf();

        $this->pro = $this->createPartialMock(
            Pro::class,
            ['setMethod', 'getApi', 'importPaymentInfo']
        );
        $this->pro->method('getApi')->willReturn($this->nvp);

        $this->transaction = $this->getMockForAbstractClass(TransactionInterface::class);
        $this->transactionRepository = $this->createPartialMock(
            TransactionRepository::class,
            ['getByTransactionType']
        );
        $this->transactionRepository->method('getByTransactionType')->willReturn($this->transaction);

        $this->express = $objectManager->getObject(
            Express::class,
            [
                'data' => [$this->pro],
                'transactionRepository' => $this->transactionRepository,
            ]
        );

        $this->paymentInstance = $this->getMockForAbstractClass(MethodInterface::class);
        $this->payment = $this->createPartialMock(
            Payment::class,
            [
                'getAmountAuthorized',
                'getMethod',
                'getMethodInstance',
                'getId',
                'getOrder',
                'addTransaction',
                'addTransactionCommentsToOrder',
                'setAmountAuthorized',
            ]
        );
        $this->payment->method('getMethodInstance')
            ->willReturn($this->paymentInstance);

        $this->payment->method('addTransaction')
            ->willReturn($this->transaction);
    }

    /**
     * Tests payment authorization flow for order.
     *
     * @throws LocalizedException
     */
    public function testAuthorizeOrder()
    {
        $this->order = $this->createPartialMock(
            Order::class,
            ['getId', 'getPayment', 'getTotalDue', 'getBaseTotalDue']
        );
        $this->order->method('getPayment')
            ->willReturn($this->payment);
        $this->order->method('getId')
            ->willReturn(1);

        $totalDue = 15;
        $baseTotalDue = 10;

        $this->order->method('getTotalDue')
            ->willReturn($totalDue);
        $this->order->method('getBaseTotalDue')
            ->willReturn($baseTotalDue);

        $this->payment->method('getMethod')
            ->willReturn('paypal_express');
        $this->payment->method('getId')
            ->willReturn(1);
        $this->payment->method('getOrder')
            ->willReturn($this->order);
        $this->payment->method('getAmountAuthorized')
            ->willReturn(0);

        $this->paymentInstance->method('getConfigPaymentAction')
            ->willReturn('order');

        $this->nvp->expects(static::once())
            ->method('callDoAuthorization')
            ->willReturnSelf();

        $this->payment->expects(static::once())
            ->method('addTransaction')
            ->with(Transaction::TYPE_AUTH)
            ->willReturn($this->transaction);

        $this->payment->method('addTransactionCommentsToOrder')
            ->with($this->transaction);

        $this->payment->method('setAmountAuthorized')
            ->with($totalDue);

        $this->express->authorizeOrder($this->order);
    }

    /**
     * Checks if payment authorization is allowed.
     *
     * @param string $method
     * @param string $action
     * @param float $authorizedAmount
     * @param bool $isAuthAllowed
     * @throws LocalizedException
     * @dataProvider paymentDataProvider
     */
    public function testIsOrderAuthorizationAllowed(
        string $method,
        string $action,
        float $authorizedAmount,
        bool $isAuthAllowed
    ) {
        $this->payment->method('getMethod')
            ->willReturn($method);

        $this->paymentInstance->method('getConfigPaymentAction')
            ->willReturn($action);

        $this->payment->method('getAmountAuthorized')
            ->willReturn($authorizedAmount);

        static::assertEquals($isAuthAllowed, $this->express->isOrderAuthorizationAllowed($this->payment));
    }

    /**
     * Data provider for payment methods call.
     *
     * @return array
     */
    public function paymentDataProvider(): array
    {
        return [
            ['paypal_express', 'sale', 10, false],
            ['paypal_express', 'order', 50, false],
            ['paypal_express', 'capture', 0, false],
            ['paypal_express', 'order', 0, true],
            ['braintree', 'authorize', 10, false],
            ['braintree', 'authorize', 0, false],
        ];
    }
}
