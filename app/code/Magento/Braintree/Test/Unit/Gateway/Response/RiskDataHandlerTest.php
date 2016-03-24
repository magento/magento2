<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Response;

use Braintree\RiskData;
use Braintree\Transaction;
use Magento\Sales\Model\Order\Payment;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Braintree\Gateway\Response\RiskDataHandler;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

/**
 * Class RiskDataHandlerTest
 *
 * @see \Magento\Braintree\Gateway\Response\RiskDataHandler
 */
class RiskDataHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RiskDataHandler
     */
    private $riskDataHandler;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReaderMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->riskDataHandler = new RiskDataHandler($this->subjectReaderMock);
    }

    /**
     * Run test for handle method
     */
    public function testHandle()
    {
        $paymentData = $this->getPaymentDataObjectMock();
        $transaction = $this->getBraintreeTransactionMock();

        $response = [
            'object' => $transaction
        ];
        $handlingSubject = [
            'payment' =>$paymentData,
        ];

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($handlingSubject)
            ->willReturn($paymentData);
        $this->subjectReaderMock->expects(self::once())
            ->method('readTransaction')
            ->with($response)
            ->willReturn($transaction);

        $this->riskDataHandler->handle($handlingSubject, $response);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getBraintreeTransactionMock()
    {
        $transaction = \Braintree\Transaction::factory([]);
        $transaction->_set(
            'riskData',
            RiskData::factory(
                [
                    'id' => 'test-id',
                    'decision' => 'test-decision',
                ]
            )
        );

        return $transaction;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPaymentDataObjectMock()
    {
        $mock = $this->getMockBuilder(PaymentDataObjectInterface::class)
            ->getMockForAbstractClass();

        $mock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->getPaymentMock());

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPaymentMock()
    {
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentMock->expects(self::at(0))
            ->method('setAdditionalInformation')
            ->with(RiskDataHandler::RISK_DATA_ID, 'test-id');
        $paymentMock->expects(self::at(1))
            ->method('setAdditionalInformation')
            ->with(RiskDataHandler::RISK_DATA_DECISION, 'test-decision');

        return $paymentMock;
    }
}
