<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Response;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\Response\TransactionDetailsResponseHandler;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransactionDetailsResponseHandlerTest extends TestCase
{
    /**
     * @var TransactionDetailsResponseHandler
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
     * @var Config|MockObject
     */
    private $configMock;

    protected function setUp(): void
    {
        $this->paymentDOMock = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->configMock = $this->createMock(Config::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->handler = new TransactionDetailsResponseHandler(new SubjectReader(), $this->configMock);
    }

    public function testHandle()
    {
        $subject = [
            'payment' => $this->paymentDOMock,
            'store_id' => 123,
        ];
        $response = [
            'transactionResponse' => [
                'dontsaveme' => 'dontdoti',
                'abc' => 'foobar',
            ]
        ];

        // Assert the information comes from the right store config
        $this->configMock->method('getAdditionalInfoKeys')
            ->with(123)
            ->willReturn(['abc']);

        // Assert the payment has the most recent information always set on it
        $this->paymentMock->expects($this->once())
            ->method('setAdditionalInformation')
            ->with('abc', 'foobar');
        // Assert the transaction has the raw details from the transaction
        $this->paymentMock->expects($this->once())
            ->method('setTransactionAdditionalInfo')
            ->with('raw_details_info', ['abc' => 'foobar']);

        $this->handler->handle($subject, $response);
        // Assertions are via mock expects above
    }
}
