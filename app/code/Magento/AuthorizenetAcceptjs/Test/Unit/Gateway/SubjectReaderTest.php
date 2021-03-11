<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use PHPUnit\Framework\TestCase;

class SubjectReaderTest extends TestCase
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->subjectReader = new SubjectReader();
    }

    public function testReadPayment(): void
    {
        $paymentDO = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);

        $this->assertSame($paymentDO, $this->subjectReader->readPayment(['payment' => $paymentDO]));
    }

    /**
     */
    public function testReadPaymentThrowsExceptionWhenNotAPaymentObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment data object should be provided');

        $this->subjectReader->readPayment(['payment' => 'nope']);
    }

    /**
     */
    public function testReadPaymentThrowsExceptionWhenNotSet(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment data object should be provided');

        $this->subjectReader->readPayment([]);
    }

    public function testReadResponse(): void
    {
        $expected = ['foo' => 'bar'];

        $this->assertSame($expected, $this->subjectReader->readResponse(['response' => $expected]));
    }

    /**
     */
    public function testReadResponseThrowsExceptionWhenNotAvailable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Response does not exist');

        $this->subjectReader->readResponse([]);
    }

    public function testReadStoreId(): void
    {
        $this->assertEquals(123, $this->subjectReader->readStoreId(['store_id' => '123']));
    }

    public function testReadStoreIdFromOrder(): void
    {
        $paymentDOMock = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);
        $orderMock = $this->getMockForAbstractClass(OrderAdapterInterface::class);
        $paymentDOMock->method('getOrder')
            ->willReturn($orderMock);
        $orderMock->method('getStoreID')
            ->willReturn('123');

        $result = $this->subjectReader->readStoreId([
            'payment' => $paymentDOMock
        ]);

        $this->assertEquals(123, $result);
    }

    public function testReadLoginId(): void
    {
        $this->assertEquals('abc', $this->subjectReader->readLoginId([
            'merchantAuthentication' => ['name' => 'abc']
        ]));
    }

    public function testReadTransactionKey(): void
    {
        $this->assertEquals('abc', $this->subjectReader->readTransactionKey([
            'merchantAuthentication' => ['transactionKey' => 'abc']
        ]));
    }

    public function testReadAmount(): void
    {
        $this->assertSame('123.12', $this->subjectReader->readAmount(['amount' => 123.12]));
    }

    /**
     */
    public function testReadAmountThrowsExceptionWhenNotAvailable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount should be provided');

        $this->subjectReader->readAmount([]);
    }
}
