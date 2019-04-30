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
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Response\PaymentResponseHandler
 */
class PaymentResponseHandlerTest extends TestCase
{
    /**
     * @var int
     */
    private $responseCodeApproved = 1;

    /**
     * @var PaymentResponseHandler
     */
    private $builder;

    /**
     * @var Payment|MockObject
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

        $this->builder = new PaymentResponseHandler(new SubjectReader());
    }

    /**
     * @return void
     */
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
                'responseCode' => $this->responseCodeApproved,
            ]
        ];
        $subject = [
            'payment' => $this->paymentDOMock,
        ];

        $this->builder->handle($subject, $response);
        // Assertions are part of mocking above
    }

    /**
     * @return void
     */
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
                'responseCode' => PaymentResponseHandler::RESPONSE_CODE_HELD,
            ],
        ];
        $subject = [
            'payment' => $this->paymentDOMock,
        ];

        $this->builder->handle($subject, $response);
        // Assertions are part of mocking above
    }
}
