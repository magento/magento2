<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class SubjectReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->subjectReader = new SubjectReader();
    }

    public function testReadPayment(): void
    {
        $paymentDO = $this->getMockBuilder(PaymentDataObjectInterface::class)->getMock();

        $this->assertSame($paymentDO, $this->subjectReader->readPayment(['payment' => $paymentDO]));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Payment data object should be provided
     */
    public function testReadPaymentThrowsExceptionWhenNotAPaymentObject(): void
    {
        $this->subjectReader->readPayment(['payment' => 'nope']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Payment data object should be provided
     */
    public function testReadPaymentThrowsExceptionWhenNotSet(): void
    {
        $this->subjectReader->readPayment([]);
    }

    public function testReadResponse(): void
    {
        $expected = ['foo' => 'bar'];

        $this->assertSame($expected, $this->subjectReader->readResponse(['response' => $expected]));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Response does not exist
     */
    public function testReadResponseThrowsExceptionWhenNotAvailable(): void
    {
        $this->subjectReader->readResponse([]);
        $this->subjectReader->readResponse(['response' => 123]);
    }

    public function testReadStoreId(): void
    {
        $this->assertEquals(123, $this->subjectReader->readStoreId(['store_id' => '123']));
    }

    public function testReadStoreIdFromOrder(): void
    {
        $paymentDOMock = $this->getMockBuilder(PaymentDataObjectInterface::class)
            ->getMock();
        $orderMock = $this->getMockBuilder(OrderAdapterInterface::class)
            ->getMock();
        $paymentDOMock->method('getOrder')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('getStoreID')
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
}
