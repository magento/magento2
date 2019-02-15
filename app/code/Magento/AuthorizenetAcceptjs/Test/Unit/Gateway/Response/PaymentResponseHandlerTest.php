<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Response;

use Magento\AuthorizenetAcceptjs\Gateway\Response\PaymentResponseHandler;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaymentResponseHandlerTest extends TestCase
{
    private const RESPONSE_CODE_APPROVED = 1;
    private const RESPONSE_CODE_HELD = 4;

    /**
     * @var PaymentResponseHandler
     */
    private $builder;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentDOMock;

    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->builder = new PaymentResponseHandler(new SubjectReader());
    }

    public function testHandleDefaultResponse()
    {
        $this->paymentMock->method('getAdditionalInformation')
            ->with('ccLast4')
            ->willReturn('1234');
        // Assert the avs code is saved
        $this->paymentMock->expects($this->once())
            ->method('setCcAvsStatus')
            ->with('avshurray');
        $this->paymentMock->expects($this->once())
            ->method('setCcLast4')
            ->with('1234');
        $this->paymentMock->expects($this->once())
            ->method('setIsTransactionClosed')
            ->with(false);

        $response = [
            'transactionResponse' => [
                'avsResultCode' => 'avshurray',
                'responseCode' => self::RESPONSE_CODE_APPROVED,
            ]
        ];
        $subject = [
            'payment' => $this->paymentDOMock
        ];

        $this->builder->handle($subject, $response);
        // Assertions are part of mocking above
    }

    public function testHandleHeldResponse()
    {
        // Assert the avs code is saved
        $this->paymentMock->expects($this->once())
            ->method('setCcAvsStatus')
            ->with('avshurray');
        $this->paymentMock->expects($this->once())
            ->method('setIsTransactionClosed')
            ->with(false);
        // opaque data wasn't provided
        $this->paymentMock->expects($this->never())
            ->method('setAdditionalInformation');
        // Assert the payment is flagged for review
        $this->paymentMock->expects($this->once())
            ->method('setIsTransactionPending')
            ->with(true)
            ->willReturnSelf();
        $this->paymentMock->expects($this->once())
            ->method('setIsFraudDetected')
            ->with(true);

        $response = [
            'transactionResponse' => [
                'avsResultCode' => 'avshurray',
                'responseCode' => self::RESPONSE_CODE_HELD,
            ]
        ];
        $subject = [
            'payment' => $this->paymentDOMock
        ];

        $this->builder->handle($subject, $response);
        // Assertions are part of mocking above
    }
}
