<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Response;

use Braintree\RiskData;
use Braintree\Transaction;
use Magento\Sales\Model\Order\Payment;
use Magento\BraintreeTwo\Gateway\Response\RiskDataHandler;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

/**
 * Class RiskDataHandlerTest
 *
 * @see \Magento\BraintreeTwo\Gateway\Response\RiskDataHandler
 */
class RiskDataHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RiskDataHandler
     */
    private $riskDataHandler;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->riskDataHandler = new RiskDataHandler();
    }

    /**
     * Run test for handle method
     */
    public function testHandle()
    {
        $response = [
            'object' => $this->getBraintreeTransactionMock()
        ];
        $handlingSubject = [
            'payment' => $this->getPaymentDataObjectMock(),
        ];

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

        $mock = $this->getMockBuilder(\Braintree\Result\Successful::class)
            ->disableOriginalConstructor()
            ->setMethods(['__get'])
            ->getMock();

        $mock->expects(static::once())
            ->method('__get')
            ->with('transaction')
            ->willReturn($transaction);

        return $mock;
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
