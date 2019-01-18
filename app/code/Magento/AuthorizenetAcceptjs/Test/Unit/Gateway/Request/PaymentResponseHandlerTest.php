<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Response\PaymentResponseHandler;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;

class PaymentResponseHandlerTest extends \PHPUnit\Framework\TestCase
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
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->builder = new PaymentResponseHandler(new SubjectReader());
    }

    public function testHandleDefaultResponse()
    {
        $this->paymentMock->method('getParentTransactionId')
            ->willReturn(null);
        // Assert the id is set
        $this->paymentMock->expects($this->once())
            ->method('setTransactionId')
            ->with('thetransid');
        // Assert the id is set in the additional info for later
        $this->paymentMock->expects($this->once())
            ->method('setTransactionAdditionalInfo')
            ->with('real_transaction_id', 'thetransid');
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

        $response = [
            'transactionResponse' => [
                'transId' => 'thetransid',
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

    public function testHandleDifferenceInTransactionId()
    {
        $this->paymentMock->method('getParentTransactionId')
            ->willReturn('somethingElse');
        // Assert the id is set
        $this->paymentMock->expects($this->once())
            ->method('setTransactionId')
            ->with('thetransid');

        $response = [
            'transactionResponse' => [
                'transId' => 'thetransid',
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
        $this->paymentMock->method('getParentTransactionId')
            ->willReturn(null);
        // Assert the id is set
        $this->paymentMock->expects($this->once())
            ->method('setTransactionId')
            ->with('thetransid');
        // Assert the id is set in the additional info for later
        $this->paymentMock->expects($this->once())
            ->method('setTransactionAdditionalInfo')
            ->with('real_transaction_id', 'thetransid');
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
                'transId' => 'thetransid',
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

    public function testHandleOpaqueData()
    {
        $this->paymentMock->method('getParentTransactionId')
            ->willReturn(null);
        // Assert data is added
        $this->paymentMock->expects($this->once())
            ->method('addData')
            ->with([
                'opaqueDataDescriptor' => 'descriptor',
                'opaqueDataValue' => 'value',
            ]);

        $response = [
            'transactionResponse' => [
                'transId' => 'thetransid',
                'avsResultCode' => 'avshurray',
                'responseCode' => self::RESPONSE_CODE_APPROVED,
                'userFields' => [
                    [
                        'name' => 'opaqueDataDescriptor',
                        'value' => 'descriptor'
                    ],
                    [
                        'name' => 'opaqueDataValue',
                        'value' => 'value'
                    ]
                ]
            ]
        ];
        $subject = [
            'payment' => $this->paymentDOMock
        ];

        $this->builder->handle($subject, $response);
        // Assertions are part of mocking above
    }
}
