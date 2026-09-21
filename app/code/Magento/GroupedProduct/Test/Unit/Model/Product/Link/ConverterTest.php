<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Model\Product\Link;

use Magento\Catalog\Model\Product;
use Magento\GroupedProduct\Model\Product\Link\ProductEntity\Converter;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Converter
 */
class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    private Converter $converter;

    protected function setUp(): void
    {
        $this->converter = new Converter();
    }

    public function testConvertUsesLinkQtyWhenAvailable(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getTypeId')->willReturn('simple');
        $product->method('getSku')->willReturn('sku-123');
        $product->method('getPosition')->willReturn(5);
        $product->method('getData')
            ->with(Grouped::GROUPED_LINK_QTY)
            ->willReturn(10);

        $result = $this->converter->convert($product);

        $this->assertSame('simple', $result['type']);
        $this->assertSame('sku-123', $result['sku']);
        $this->assertSame(5, $result['position']);
        $this->assertSame(
            [
                ['attribute_code' => 'qty', 'value' => 10]
            ],
            $result['custom_attributes']
        );
    }

    public function testConvertFallsBackToQtyIfLinkQtyIsNull(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getTypeId')->willReturn('simple');
        $product->method('getSku')->willReturn('sku-456');
        $product->method('getPosition')->willReturn(7);
        $product->method('getData')
            ->with(Grouped::GROUPED_LINK_QTY)
            ->willReturn(null);
        $product->method('getQty')->willReturn(3);

        $result = $this->converter->convert($product);

        $this->assertSame(
            [
                ['attribute_code' => 'qty', 'value' => 3]
            ],
            $result['custom_attributes']
        );
    }
}
