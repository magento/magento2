<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Braintree\Gateway\Request\RefundDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment;

class RefundDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RefundDataBuilder
     */
    private $dataBuilder;

    public function setUp()
    {
        $this->dataBuilder = new RefundDataBuilder(new SubjectReader());
    }

    public function testBuild()
    {
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $paymentModel = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $buildSubject = ['payment' => $paymentDO, 'amount' => 12.358];
        $transactionId = 'xsd7n';

        $paymentDO->method('getPayment')
            ->willReturn($paymentModel);
        $paymentModel->method('getParentTransactionId')
            ->willReturn($transactionId);

        self::assertEquals(
            [
                'transaction_id' => $transactionId,
                PaymentDataBuilder::AMOUNT => '12.36'
            ],
            $this->dataBuilder->build($buildSubject)
        );
    }

    public function testBuildNullAmount()
    {
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $paymentModel = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $buildSubject = ['payment' => $paymentDO];
        $transactionId = 'xsd7n';

        $paymentDO->method('getPayment')
            ->willReturn($paymentModel);
        $paymentModel->method('getParentTransactionId')
            ->willReturn($transactionId);

        self::assertEquals(
            [
                'transaction_id' => $transactionId,
                PaymentDataBuilder::AMOUNT => null
            ],
            $this->dataBuilder->build($buildSubject)
        );
    }

    public function testBuildCutOffLegacyTransactionIdPostfix()
    {
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);
        $paymentModel = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $buildSubject = ['payment' => $paymentDO];
        $legacyTxnId = 'xsd7n-' . TransactionInterface::TYPE_CAPTURE;
        $transactionId = 'xsd7n';

        $paymentDO->method('getPayment')
            ->willReturn($paymentModel);
        $paymentModel->method('getParentTransactionId')
            ->willReturn($legacyTxnId);

        self::assertEquals(
            [
                'transaction_id' => $transactionId,
                PaymentDataBuilder::AMOUNT => null
            ],
            $this->dataBuilder->build($buildSubject)
        );
    }
}
