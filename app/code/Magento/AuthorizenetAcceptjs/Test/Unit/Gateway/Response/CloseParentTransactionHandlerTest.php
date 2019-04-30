<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Response;

use Magento\AuthorizenetAcceptjs\Gateway\Response\CloseParentTransactionHandler;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Response\CloseParentTransactionHandler
 */
class CloseParentTransactionHandlerTest extends TestCase
{
    /**
     * @var CloseParentTransactionHandler
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

        $this->handler = new CloseParentTransactionHandler(new SubjectReader());
    }

    /**
     * @return void
     */
    public function testHandleClosesTransactionByDefault()
    {
        $subject = [
            'payment' => $this->paymentDOMock,
        ];
        $response = [
            'transactionResponse' => [],
        ];

        // Assert the parent transaction i closed
        $this->paymentMock->expects($this->once())
            ->method('setShouldCloseParentTransaction')
            ->with(true);

        $this->handler->handle($subject, $response);
        // Assertions are via mock expects above
    }
}
