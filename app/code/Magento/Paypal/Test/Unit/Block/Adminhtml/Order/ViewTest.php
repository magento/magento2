<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Block\Adminhtml\Order;

use Magento\Paypal\Model\Adminhtml\Express;
use Magento\Paypal\Block\Adminhtml\Order\View;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Exception\LocalizedException;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ViewTest extends TestCase
{
    /**
     * @var View
     */
    private $view;

    /**
     * @var Express|MockObject
     */
    private $express;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var Order|MockObject
     */
    private $order;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->order = $this->createPartialMock(
            Order::class,
            ['canUnhold', 'isPaymentReview', 'getState', 'isCanceled', 'getPayment']
        );

        $this->express = $this->createPartialMock(
            Express::class,
            ['isOrderAuthorizationAllowed']
        );

        $this->payment = $this->createMock(Payment::class);

        $this->view = $objectManager->getObject(
            View::class,
            [
                'express' => $this->express,
                'data' => []
            ]
        );
    }

    /**
     * Tests if authorization action is allowed for order.
     *
     * @param bool $canUnhold
     * @param bool $isPaymentReview
     * @param bool $isCanceled
     * @param bool $authAllowed
     * @param string $orderState
     * @param bool $canAuthorize
     * @throws LocalizedException
     * @dataProvider orderDataProvider
     */
    public function testIsOrderAuthorizationAllowed(
        $canUnhold,
        $isPaymentReview,
        $isCanceled,
        $authAllowed,
        $orderState,
        $canAuthorize
    ) {
        $this->order->method('canUnhold')
            ->willReturn($canUnhold);

        $this->order->method('isPaymentReview')
            ->willReturn($isPaymentReview);

        $this->order->method('isCanceled')
            ->willReturn($isCanceled);

        $this->order->method('getState')
            ->willReturn($orderState);

        $this->order->method('getPayment')
            ->willReturn($this->payment);

        $this->express->method('isOrderAuthorizationAllowed')
            ->with($this->payment)
            ->willReturn($authAllowed);

        $this->assertEquals($canAuthorize, $this->view->canAuthorize($this->order));
    }

    /**
     * Data provider for order methods call.
     *
     * @return array
     */
    public function orderDataProvider(): array
    {
        return [
            [true, false, false, true, Order::STATE_PROCESSING, false],
            [false, true, false, true, Order::STATE_PROCESSING, false],
            [false, false, true, true, Order::STATE_PROCESSING, false],
            [false, false, false, false, Order::STATE_PROCESSING, false],
            [false, false, false, true, Order::STATE_COMPLETE, false],
            [false, false, false, true, Order::STATE_CLOSED, false],
            [false, false, false, true, Order::STATE_PROCESSING, true],
        ];
    }
}
