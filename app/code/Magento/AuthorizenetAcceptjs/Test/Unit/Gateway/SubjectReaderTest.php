<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
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

    public function testReadPaymentThrowsExceptionWhenNotAPaymentObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment data object should be provided');

        $this->subjectReader->readPayment(['payment' => 'nope']);
    }

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

    public function testReadResponseThrowsExceptionWhenNotAvailable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Response does not exist');

        $this->subjectReader->readResponse([]);
        $this->subjectReader->readResponse(['response' => 123]);
    }

    public function testReadStoreId(): void
    {
        $this->assertEquals('abc', $this->subjectReader->readStoreId(['store_id' => 'abc']));
    }
}
