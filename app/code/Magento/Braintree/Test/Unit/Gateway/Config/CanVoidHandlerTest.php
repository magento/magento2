<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Config;

use Magento\Braintree\Gateway\Config\CanVoidHandler;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment;

class CanVoidHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testHandleNotOrderPayment()
    {
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $subject = [
            'payment' => $paymentDO
        ];

        $subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $subjectReader->expects($this->once())
            ->method('readPayment')
            ->willReturn($paymentDO);

        $paymentMock = $this->createMock(InfoInterface::class);

        $paymentDO->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $voidHandler = new CanVoidHandler($subjectReader);

        $this->assertFalse($voidHandler->handle($subject));
    }

    public function testHandleSomeAmountWasPaid()
    {
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $subject = [
            'payment' => $paymentDO
        ];

        $subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $subjectReader->expects($this->once())
            ->method('readPayment')
            ->willReturn($paymentDO);

        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentDO->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $paymentMock->expects($this->once())
            ->method('getAmountPaid')
            ->willReturn(1.00);

        $voidHandler = new CanVoidHandler($subjectReader);

        $this->assertFalse($voidHandler->handle($subject));
    }
}
