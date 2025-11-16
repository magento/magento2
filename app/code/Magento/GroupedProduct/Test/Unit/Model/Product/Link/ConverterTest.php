<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Model\Product\Link;

use Magento\Catalog\Test\Unit\Helper\ProductTestHelper;
use Magento\GroupedProduct\Model\Product\Link\ProductEntity\Converter;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Converter
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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

    public function testConvertUsesInitialQtyWhenAvailable(): void
    {
        $product = $this->createPartialMock(
            ProductTestHelper::class,
            ['getTypeId', 'getSku', 'getQty', 'getPosition', 'getInitialQty']
        );

        $product->method('getTypeId')->willReturn('simple');
        $product->method('getSku')->willReturn('sku-123');
        $product->method('getPosition')->willReturn(5);

        // Qty override
        $product->method('getInitialQty')->willReturn(10);
        $product->method('getQty')->willReturn(2); // fallback should NOT be used

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

    public function testConvertFallsBackToQtyIfInitialQtyIsNull(): void
    {
        $product = $this->createPartialMock(
            ProductTestHelper::class,
            ['getTypeId', 'getSku', 'getQty', 'getPosition', 'getInitialQty']
        );

        $product->method('getTypeId')->willReturn('simple');
        $product->method('getSku')->willReturn('sku-456');
        $product->method('getPosition')->willReturn(7);
        $product->method('getInitialQty')->willReturn(null);
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
