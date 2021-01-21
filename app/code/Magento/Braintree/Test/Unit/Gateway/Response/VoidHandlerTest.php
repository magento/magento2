<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Response;

use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Gateway\Response\VoidHandler;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;

class VoidHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testHandle()
    {
        $paymentDO = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $paymentInfo = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handlingSubject = [
            'payment' => $paymentDO
        ];

        $transaction = \Braintree\Transaction::factory(['id' => 1]);
        $response = [
            'object' => new \Braintree\Result\Successful($transaction, 'transaction')
        ];

        $subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $subjectReader->expects(static::once())
            ->method('readPayment')
            ->with($handlingSubject)
            ->willReturn($paymentDO);
        $paymentDO->expects(static::atLeastOnce())
            ->method('getPayment')
            ->willReturn($paymentInfo);
        $subjectReader->expects(static::once())
            ->method('readTransaction')
            ->with($response)
            ->willReturn($transaction);

        $paymentInfo->expects(static::never())
            ->method('setTransactionId');

        $paymentInfo->expects(static::once())
            ->method('setIsTransactionClosed')
            ->with(true);
        $paymentInfo->expects(static::once())
            ->method('setShouldCloseParentTransaction')
            ->with(true);

        $handler = new VoidHandler($subjectReader);
        $handler->handle($handlingSubject, $response);
    }
}
