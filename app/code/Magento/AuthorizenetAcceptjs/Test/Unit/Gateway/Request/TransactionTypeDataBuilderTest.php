<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\Request\TransactionTypeDataBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetAcceptjs\Model\PassthroughDataObject;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransactionTypeDataBuilderTest extends TestCase
{
    /**
     * @var TransactionTypeDataBuilder
     */
    private $builder;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentDOMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var PassthroughDataObject
     */
    private $passthroughData;

    protected function setUp()
    {
        $this->configMock = $this->createMock(Config::class);
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);
        $this->passthroughData = new PassthroughDataObject();

        $this->builder = new TransactionTypeDataBuilder(
            new SubjectReader(),
            $this->configMock,
            $this->passthroughData
        );
    }

    public function testBuildWillCaptureWhenAuthorizeTransactionExists()
    {
        $transactionMock = $this->createMock(Payment\Transaction::class);
        $transactionMock->method('getAdditionalInformation')
            ->with('real_transaction_id')
            ->willReturn('prevtrans');
        $this->paymentMock->method('getAuthorizationTransaction')
            ->willReturn($transactionMock);

        $expected = [
            'transactionRequest' => [
                'transactionType' => 'priorAuthCaptureTransaction',
                'refTransId' => 'prevtrans'
            ]
        ];

        $buildSubject = [
            'payment' => $this->paymentDOMock,
        ];

        $this->assertEquals($expected, $this->builder->build($buildSubject));
        $this->assertEquals('priorAuthCaptureTransaction', $this->passthroughData->getData('transactionType'));
    }

    /**
     * @dataProvider defaultActionProvider
     */
    public function testBuildWillPerformDefaultActionWhenAuthorizeTransactionDoesntExists($configValue, $expectedType)
    {
        $this->configMock->method('getPaymentAction')
            ->with(123)
            ->willReturn($configValue);
        $this->paymentMock->method('getAuthorizationTransaction')
            ->willReturn(null);

        $expected = [
            'transactionRequest' => [
                'transactionType' => $expectedType
            ]
        ];

        $buildSubject = [
            'store_id' => 123,
            'payment' => $this->paymentDOMock,
        ];

        $this->assertEquals($expected, $this->builder->build($buildSubject));
        $this->assertEquals($expectedType, $this->passthroughData->getData('transactionType'));
    }

    public function defaultActionProvider()
    {
        return [
            ['authorize', 'authOnlyTransaction'],
            ['authorize_capture', 'authCaptureTransaction'],
            ['someothervalue', 'authCaptureTransaction'],
        ];
    }
}
