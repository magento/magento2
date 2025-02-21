<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Test\Unit\Model\Resolver;

use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\Api\ExtensionAttributesInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\SalesGraphQl\Model\Resolver\OrderTotal;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\LocalizedException;

class OrderTotalTest extends TestCase
{
    /**
     * @var OrderInterface|MockObject
     */
    private $orderMock;

    /**
     * @var OrderTotal|MockObject
     */
    private $orderTotal;

    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @var Field|MockObject
     */
    private $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;

    /**
     * @var ExtensionAttributesInterface|MockObject
     */
    private $extensionAttributesMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ContextInterface::class);
        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);
        $this->orderMock = $this->createMock(OrderInterface::class);
        $this->orderMock->method('getOrderCurrencyCode')->willReturn('USD');
        $this->orderMock->method('getBaseCurrencyCode')->willReturn('USD');
        $this->orderMock->method('getBaseGrandTotal')->willReturn(100.00);
        $this->orderMock->method('getGrandTotal')->willReturn(110.00);
        $this->orderMock->method('getSubtotal')->willReturn(110.00);
        $this->orderMock->method('getTaxAmount')->willReturn(10.00);
        $this->orderMock->method('getShippingAmount')->willReturn(5.00);
        $this->orderMock->method('getShippingInclTax')->willReturn(7.00);
        $this->orderMock->method('getDiscountAmount')->willReturn(7.00);
        $this->orderMock->method('getDiscountDescription')->willReturn('TEST123');
        $this->orderTotal = new OrderTotal();
    }

    public function testResolve(): void
    {
        $fieldMock = $this->createMock(Field::class);
        $resolveInfoMock = $this->createMock(ResolveInfo::class);
        $value = ['model' => $this->orderMock];
        $args = [];
        $this->extensionAttributesMock = $this->getMockBuilder(ExtensionAttributesInterface::class)
            ->addMethods(['getAppliedTaxes', 'getItemAppliedTaxes'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionAttributesMock->expects($this->once())->method('getAppliedTaxes')->willReturn([]);
        $this->extensionAttributesMock->expects($this->once())->method('getItemAppliedTaxes')->willReturn([]);
        $this->orderMock->method('getExtensionAttributes')->willReturn($this->extensionAttributesMock);
        $result = $this->orderTotal->resolve($fieldMock, $this->contextMock, $resolveInfoMock, $value, $args);
        $this->assertArrayHasKey('base_grand_total', $result);
        $this->assertEquals(100.00, $result['base_grand_total']['value']);
        $this->assertEquals('USD', $result['base_grand_total']['currency']);
        $this->assertArrayHasKey('grand_total', $result);
        $this->assertEquals(110.00, $result['grand_total']['value']);
        $this->assertEquals('USD', $result['grand_total']['currency']);
        $this->assertArrayHasKey('subtotal', $result);
        $this->assertEquals(110.00, $result['subtotal']['value']);
        $this->assertEquals('USD', $result['subtotal']['currency']);
        $this->assertArrayHasKey('total_tax', $result);
        $this->assertEquals(10.00, $result['total_tax']['value']);
        $this->assertEquals('USD', $result['total_tax']['currency']);
        $this->assertArrayHasKey('discounts', $result);
        foreach ($result['discounts'] as $discount) {
            $this->assertEquals('TEST123', $discount['label']);
            $this->assertEquals(7.00, $discount['amount']['value']);
            $this->assertEquals('USD', $discount['amount']['currency']);
            $this->assertArrayHasKey('order_model', $discount);
        }
    }

    public function testResolveThrowsExceptionForMissingModelValue(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"model" value should be specified');
        $value = ['model' => null];
        $args = [];

        $this->orderTotal->resolve($this->fieldMock, $this->contextMock, $this->resolveInfoMock, $value, $args);
    }
}
