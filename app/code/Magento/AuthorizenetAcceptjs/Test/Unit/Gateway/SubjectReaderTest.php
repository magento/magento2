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

/**
 * Test for Magento\AuthorizenetAcceptjs\Gateway\SubjectReader
 */
class SubjectReaderTest extends TestCase
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

    /**
     * @return void
     */
    public function testReadPayment()
    {
        $paymentDO = $this->createMock(PaymentDataObjectInterface::class);

        $this->assertSame($paymentDO, $this->subjectReader->readPayment(['payment' => $paymentDO]));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Payment data object should be provided
     *
     * @return void
     */
    public function testReadPaymentThrowsExceptionWhenNotAPaymentObject()
    {
        $this->subjectReader->readPayment(['payment' => 'nope']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Payment data object should be provided
     *
     * @return void
     */
    public function testReadPaymentThrowsExceptionWhenNotSet()
    {
        $this->subjectReader->readPayment([]);
    }

    /**
     * @return void
     */
    public function testReadResponse()
    {
        $expected = ['foo' => 'bar'];

        $this->assertSame($expected, $this->subjectReader->readResponse(['response' => $expected]));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Response does not exist
     *
     * @return void
     */
    public function testReadResponseThrowsExceptionWhenNotAvailable()
    {
        $this->subjectReader->readResponse([]);
    }

    /**
     * @return void
     */
    public function testReadStoreId()
    {
        $this->assertEquals(123, $this->subjectReader->readStoreId(['store_id' => '123']));
    }

    /**
     * @return void
     */
    public function testReadStoreIdFromOrder()
    {
        $paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $orderMock = $this->createMock(OrderAdapterInterface::class);
        $paymentDOMock->method('getOrder')
            ->willReturn($orderMock);
        $orderMock->method('getStoreID')
            ->willReturn('123');

        $result = $this->subjectReader->readStoreId([
            'payment' => $paymentDOMock,
        ]);

        $this->assertEquals(123, $result);
    }

    /**
     * @return void
     */
    public function testReadLoginId()
    {
        $this->assertEquals('abc', $this->subjectReader->readLoginId([
            'merchantAuthentication' => ['name' => 'abc'],
        ]));
    }

    /**
     * @return void
     */
    public function testReadTransactionKey()
    {
        $this->assertEquals('abc', $this->subjectReader->readTransactionKey([
            'merchantAuthentication' => ['transactionKey' => 'abc'],
        ]));
    }

    /**
     * @return void
     */
    public function testReadAmount()
    {
        $this->assertSame('123.12', $this->subjectReader->readAmount(['amount' => 123.12]));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Amount should be provided
     *
     * @return void
     */
    public function testReadAmountThrowsExceptionWhenNotAvailable()
    {
        $this->subjectReader->readAmount([]);
    }
}
