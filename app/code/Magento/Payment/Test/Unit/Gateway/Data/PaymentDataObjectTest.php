<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Data;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Model\InfoInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests for PaymentDataObject
 */
class PaymentDataObjectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentDataObject
     */
    protected $model;

    /**
     * @var OrderAdapterInterface|MockObject
     */
    protected $orderMock;

    /**
     * @var InfoInterface|\MockObject
     */
    protected $paymentMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->orderMock = $this->getMockBuilder(OrderAdapterInterface::class)
            ->getMockForAbstractClass();

        $this->paymentMock = $this->getMockBuilder(InfoInterface::class)
            ->getMockForAbstractClass();

        $this->model = new PaymentDataObject($this->orderMock, $this->paymentMock);
    }

    /**
     * Verify can get order
     *
     * @return void
     */
    public function testGetOrder(): void
    {
        $this->assertSame($this->orderMock, $this->model->getOrder());
    }

    /**
     * Verify can get payment
     *
     * @return void
     */
    public function testGetPayment(): void
    {
        $this->assertSame($this->paymentMock, $this->model->getPayment());
    }
}
