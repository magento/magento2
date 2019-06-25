<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Request\CaptureDataBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetAcceptjs\Model\PassthroughDataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Gateway\Request\CaptureDataBuilder
 */
class CaptureDataBuilderTest extends TestCase
{
    /**
     * @var CaptureDataBuilder
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
     * @var PassthroughDataObject
     */
    private $passthroughData;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);
        $this->passthroughData = new PassthroughDataObject();

        $this->builder = $objectManagerHelper->getObject(
            CaptureDataBuilder::class,
            [
                'subjectReader' => new SubjectReader(),
                'passthroughData' => $this->passthroughData,
            ]
        );
    }

    /**
     * @return void
     */
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
                'refTransId' => 'prevtrans',
            ],
        ];

        $buildSubject = [
            'payment' => $this->paymentDOMock,
        ];

        $this->assertEquals($expected, $this->builder->build($buildSubject));
        $this->assertEquals('priorAuthCaptureTransaction', $this->passthroughData->getData('transactionType'));
    }
}
