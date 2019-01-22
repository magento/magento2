<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\Request\AuthenticationDataBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;

class AuthenticationDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AuthenticationDataBuilder
     */
    private $builder;

    /**
     * @var Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var Payment|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentDOMock;

    /**
     * @var SubjectReader|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subjectReaderMock;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject|SubjectReader subjectReaderMock */
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->builder = new AuthenticationDataBuilder($this->subjectReaderMock, $this->configMock);
    }

    public function testBuild()
    {
        $this->configMock->method('getLoginId')
            ->willReturn('myloginid');
        $this->configMock->method('getTransactionKey')
            ->willReturn('mytransactionkey');

        $expected = [
            'merchantAuthentication' => [
                'name' => 'myloginid',
                'transactionKey' => 'mytransactionkey'
            ]
        ];

        $buildSubject = [];

        $this->subjectReaderMock->method('readStoreId')
            ->with($buildSubject)
            ->willReturn(123);

        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
