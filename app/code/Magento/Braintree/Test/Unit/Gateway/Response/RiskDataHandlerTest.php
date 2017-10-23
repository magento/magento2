<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Response;

use Braintree\Transaction;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Braintree\Gateway\Response\RiskDataHandler;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class RiskDataHandlerTest
 *
 * @see \Magento\Braintree\Gateway\Response\RiskDataHandler
 */
class RiskDataHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RiskDataHandler
     */
    private $riskDataHandler;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReader;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->subjectReader = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['readPayment', 'readTransaction'])
            ->getMock();

        $this->riskDataHandler = new RiskDataHandler($this->subjectReader);
    }

    /**
     * Test for handle method
     * @covers \Magento\Braintree\Gateway\Response\RiskDataHandler::handle
     * @param string $riskDecision
     * @param boolean $isFraud
     * @dataProvider riskDataProvider
     */
    public function testHandle($riskDecision, $isFraud)
    {
        /** @var Payment|MockObject $payment */
        $payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['setAdditionalInformation', 'setIsFraudDetected'])
            ->getMock();
        /** @var PaymentDataObjectInterface|MockObject $paymentDO */
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $paymentDO->expects(self::once())
            ->method('getPayment')
            ->willReturn($payment);

        $transaction = Transaction::factory([
            'riskData' => [
                'id' => 'test-id',
                'decision' => $riskDecision
            ]
        ]);

        $response = [
            'object' => $transaction
        ];
        $handlingSubject = [
            'payment' => $paymentDO,
        ];

        $this->subjectReader->expects(static::once())
            ->method('readPayment')
            ->with($handlingSubject)
            ->willReturn($paymentDO);
        $this->subjectReader->expects(static::once())
            ->method('readTransaction')
            ->with($response)
            ->willReturn($transaction);

        $payment->expects(static::at(0))
            ->method('setAdditionalInformation')
            ->with(RiskDataHandler::RISK_DATA_ID, 'test-id');
        $payment->expects(static::at(1))
            ->method('setAdditionalInformation')
            ->with(RiskDataHandler::RISK_DATA_DECISION, $riskDecision);

        if (!$isFraud) {
            $payment->expects(static::never())
                ->method('setIsFraudDetected');
        } else {
            $payment->expects(static::once())
                ->method('setIsFraudDetected')
                ->with(true);
        }

        $this->riskDataHandler->handle($handlingSubject, $response);
    }

    /**
     * Get list of variations to test fraud
     * @return array
     */
    public function riskDataProvider()
    {
        return [
            ['decision' => 'Not Evaluated', 'isFraud' => false],
            ['decision' => 'Approve', 'isFraud' => false],
            ['decision' => 'Review', 'isFraud' => true],
        ];
    }
}
