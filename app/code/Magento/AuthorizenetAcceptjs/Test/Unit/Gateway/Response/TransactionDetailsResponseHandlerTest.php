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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Gateway\Response\TransactionDetailsResponseHandler
 */
class TransactionDetailsResponseHandlerTest extends TestCase
{
    /**
     * @var TransactionDetailsResponseHandler
     */
    private $handler;

    /**
     * @var Payment|MockObject
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

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->configMock = $this->createMock(Config::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->handler = $objectManagerHelper->getObject(
            TransactionDetailsResponseHandler::class,
            [
                'subjectReader' => new SubjectReader(),
                'config' => $this->configMock,
            ]
        );
    }

    /**
     * @return void
     */
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
            ],
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
