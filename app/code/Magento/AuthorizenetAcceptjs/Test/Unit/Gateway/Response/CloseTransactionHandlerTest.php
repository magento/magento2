<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Response;

use Magento\AuthorizenetAcceptjs\Gateway\Response\CloseTransactionHandler;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CloseTransactionHandlerTest extends TestCase
{
    /**
     * @var CloseTransactionHandler
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

    protected function setUp(): void
    {
        $this->paymentDOMock = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->handler = new CloseTransactionHandler(new SubjectReader());
    }

    public function testHandleClosesTransactionByDefault()
    {
        $subject = [
            'payment' => $this->paymentDOMock
        ];
        $response = [
            'transactionResponse' => []
        ];

        // Assert the transaction is closed
        $this->paymentMock->expects($this->once())
            ->method('setIsTransactionClosed')
            ->with(true);

        $this->handler->handle($subject, $response);
        // Assertions are via mock expects above
    }
}
