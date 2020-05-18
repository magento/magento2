<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Gateway\Response;

use Braintree\Result\Successful;
use Braintree\Transaction;
use Magento\Braintree\Gateway\Response\VoidHandler;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;

class VoidHandlerTest extends TestCase
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

        $transaction = Transaction::factory(['id' => 1]);
        $response = [
            'object' => new Successful($transaction, 'transaction')
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
