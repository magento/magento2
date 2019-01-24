<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Request\PaymentDataBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetAcceptjs\Model\PassthroughDataObject;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;

class PaymentDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentDataBuilder
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
     * @var Order
     */
    private $orderMock;

    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);
        $this->paymentDOMock->method('getOrder')
            ->willReturn($this->orderMock);
        $this->passthroughData = new PassthroughDataObject();

        $this->builder = new PaymentDataBuilder(
            new SubjectReader(),
            $this->passthroughData
        );
    }

    public function testBuild()
    {
        $this->paymentMock->method('getAdditionalInformation')
            ->will($this->returnValueMap([
                ['opaqueDataDescriptor', 'foo'],
                ['opaqueDataValue', 'bar']
            ]));

        $this->paymentMock->method('encrypt')
            ->will($this->returnValueMap([
                ['foo', 'encfoo'],
                ['bar', 'encbar']
            ]));

        $this->orderMock->method('getBaseShippingAmount')
            ->willReturn('43.12');

        $expected = [
            'transactionRequest' => [
                'amount' => '123.45',
                'shipping' => [
                    'amount' => '43.12'
                ],
                'payment' => [
                    'opaqueData' => [
                        'dataDescriptor' => 'foo',
                        'dataValue' => 'bar'
                    ]
                ]
            ]
        ];

        $buildSubject = [
            'payment' => $this->paymentDOMock,
            'order' => $this->orderMock,
            'amount' => 123.45
        ];

        $this->assertEquals($expected, $this->builder->build($buildSubject));
        $this->assertEquals('encfoo', $this->passthroughData->getData('opaqueDataDescriptor'));
        $this->assertEquals('encbar', $this->passthroughData->getData('opaqueDataValue'));
    }
}
