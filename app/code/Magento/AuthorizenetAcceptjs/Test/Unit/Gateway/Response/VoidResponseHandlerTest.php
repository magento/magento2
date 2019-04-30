<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Response;

use Magento\AuthorizenetAcceptjs\Gateway\Response\VoidResponseHandler;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Response\VoidResponseHandler
 */
class VoidResponseHandlerTest extends TestCase
{
    /**
     * @var VoidResponseHandler
     */
    private $handler;

    /**
     * @var InfoInterface|MockObject
     */
    private $paymentMock;

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDOMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->handler = new VoidResponseHandler(new SubjectReader());
    }

    /**
     * @return void
     */
    public function testHandle()
    {
        $subject = [
            'payment' => $this->paymentDOMock,
        ];
        $response = [
            'transactionResponse' => [
                'transId' => 'abc123',
            ],
        ];

        // Assert the transaction is closed
        $this->paymentMock->expects($this->once())
            ->method('setIsTransactionClosed')
            ->with(true);
        // Assert the parent transaction is closed
        $this->paymentMock->expects($this->once())
            ->method('setShouldCloseParentTransaction')
            ->with(true);
        // Assert the authorize.net transaction id is saved
        $this->paymentMock->expects($this->once())
            ->method('setTransactionAdditionalInfo')
            ->with('real_transaction_id', 'abc123');

        $this->handler->handle($subject, $response);
        // Assertions are via mock expects above
    }
}
