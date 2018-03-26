<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Gateway\Request\VoidDataBuilder;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests \Magento\Braintree\Gateway\Request\VaultCaptureDataBuilder.
 */
class VoidDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var VoidDataBuilder
     */
    private $builder;

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDOMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReaderMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentDOMock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new VoidDataBuilder($this->subjectReaderMock);
    }

    /**
     * @param string|null $parentTransactionId
     * @param string $callLastTransId
     * @param string $lastTransId
     * @param string $expected
     * @return void
     * @dataProvider buildDataProvider
     */
    public function testBuild($parentTransactionId, $callLastTransId, $lastTransId, $expected)
    {
        $amount = 30.00;

        $buildSubject = [
            'payment' => $this->paymentDOMock,
            'amount' => $amount,
        ];

        $this->subjectReaderMock->expects(self::once())
            ->method('readPayment')
            ->with($buildSubject)
            ->willReturn($this->paymentDOMock);

        $this->paymentMock->expects(self::once())
            ->method('getParentTransactionId')
            ->willReturn($parentTransactionId);
        $this->paymentMock->expects(self::$callLastTransId())
            ->method('getLastTransId')
            ->willReturn($lastTransId);

        $result = $this->builder->build($buildSubject);
        self::assertEquals(
            ['transaction_id' => $expected],
            $result
        );
    }

    /**
     * @return array
     */
    public function buildDataProvider()
    {
        return [
            [
                'parentTransactionId' => 'b3b99d',
                'callLastTransId' => 'never',
                'lastTransId' => 'd45d22',
                'expected' => 'b3b99d',
            ],
            [
                'parentTransactionId' => null,
                'callLastTransId' => 'once',
                'expected' => 'd45d22',
                'lastTransId' => 'd45d22',
            ],
        ];
    }
}
