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

class CloseTransactionHandlerTest extends \PHPUnit\Framework\TestCase
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

    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->handler = new CloseTransactionHandler(new SubjectReader());
    }

    public function testHandleDoesntCloseAuthorizeTransactions()
    {
        $subject = [
            'payment' => $this->paymentDOMock
        ];
        $response = [
            'transactionResponse' => [
                'userFields' => [
                    [
                        'name' => 'transactionType',
                        'value' => 'authOnlyTransaction'
                    ]
                ]
            ]
        ];

        // Assert the transaction is not closed
        $this->paymentMock->expects($this->never())
            ->method('setIsTransactionClosed');
        // Assert the parent transaction is not closed
        $this->paymentMock->expects($this->never())
            ->method('setShouldCloseParentTransaction');

        $this->handler->handle($subject, $response);
        // Assertions are via mock expects above
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
        // Assert the parent transaction i closed
        $this->paymentMock->expects($this->once())
            ->method('setShouldCloseParentTransaction')
            ->with(true);

        $this->handler->handle($subject, $response);
        // Assertions are via mock expects above
    }

    /**
     * @dataProvider nonAuthorizeTransactionTypeProvider
     * @param string $transactionType
     */
    public function testHandleClosesTransactionWhenOtherTransactionTypesAreUsed(string $transactionType)
    {
        $subject = [
            'payment' => $this->paymentDOMock
        ];
        $response = [
            'transactionResponse' => [
                'userFields' => [
                    [
                        'name' => 'transactionType',
                        'value' => $transactionType
                    ]
                ]
            ]
        ];

        // Assert the transaction is closed
        $this->paymentMock->expects($this->once())
            ->method('setIsTransactionClosed')
            ->with(true);
        // Assert the parent transaction i closed
        $this->paymentMock->expects($this->once())
            ->method('setShouldCloseParentTransaction')
            ->with(true);

        $this->handler->handle($subject, $response);
        // Assertions are via mock expects above
    }

    public function nonAuthorizeTransactionTypeProvider()
    {
        return [
            ['authCaptureTransaction'],
            ['priorAuthCaptureTransaction'],
            ['refundTransaction'],
            ['somethingElseToTriggerDefaultBehavior'],
        ];
    }
}
