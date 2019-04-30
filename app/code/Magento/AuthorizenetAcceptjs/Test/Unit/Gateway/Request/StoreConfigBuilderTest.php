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
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request\StoreConfigBuilder
 */
class StoreConfigBuilderTest extends TestCase
{
    /**
     * @var StoreConfigBuilder
     */
    private $builder;

    /**
     * @var InfoInterface|MockObject
     */
    private $paymentMock;

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDOMock;

    /**
     * @var OrderAdapterInterface|MockObject
     */
    private $orderMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->paymentDOMock = $this->createMock(PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(InfoInterface::class);
        $this->paymentDOMock->method('getPayment')
            ->willReturn($this->paymentMock);
        $this->orderMock = $this->createMock(OrderAdapterInterface::class);
        $this->paymentDOMock->method('getOrder')
            ->willReturn($this->orderMock);

        $this->builder = new StoreConfigBuilder(new SubjectReader());
    }

    /**
     * @return void
     */
    public function testBuild()
    {
        $this->orderMock->method('getStoreID')
            ->willReturn(123);

        $expected = [
            'store_id' => 123,
        ];

        $buildSubject = [
            'payment' => $this->paymentDOMock,
        ];
        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
