<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Request\StoreConfigBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;

class StoreConfigBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StoreConfigBuilder
     */
    private $builder;

    /**
     * @var InfoInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMock;

    /**
     * @var PaymentDataObjectInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentDOMock;

    /**
     * @var OrderAdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderMock;

    protected function setUp()
    {
        $this->paymentDOMock = $this->getMockBuilder(PaymentDataObjectInterface::class)
            ->getMock();
        $this->paymentMock = $this->getMockBuilder(InfoInterface::class)
            ->getMock();
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);
        $this->orderMock = $this->getMockBuilder(OrderAdapterInterface::class)
            ->getMock();
        $this->paymentDOMock->method('getOrder')
            ->willReturn($this->orderMock);

        $this->builder = new StoreConfigBuilder(new SubjectReader());
    }

    public function testBuild()
    {
        $this->orderMock->expects($this->once())
            ->method('getStoreID')
            ->willReturn(123);

        $expected = [
            'store_id' => 123
        ];

        $buildSubject = [
            'payment' => $this->paymentDOMock,
        ];
        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
