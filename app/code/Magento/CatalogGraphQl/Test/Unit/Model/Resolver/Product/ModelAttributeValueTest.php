<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Product;

use Magento\CatalogGraphQl\Model\Resolver\Product\ModelAttributeValue;
use Magento\Framework\DataObject;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PHPUnit\Framework\TestCase;

class ModelAttributeValueTest extends TestCase
{
    public function testReturnsAttributeValueFromProductModel(): void
    {
        $product = new DataObject(['model' => 'MX-500']);

        self::assertSame('MX-500', $this->resolve(['sku' => 'simple', 'model' => $product]));
    }

    public function testReturnsNullWhenProductHasNoValue(): void
    {
        $product = new DataObject();

        self::assertNull($this->resolve(['sku' => 'simple', 'model' => $product]));
    }

    public function testFallsBackToRawValueWithoutProductModel(): void
    {
        self::assertSame('MX-500', $this->resolve(['sku' => 'simple', 'model' => 'MX-500']));
        self::assertNull($this->resolve(['sku' => 'simple']));
    }

    /**
     * @param array $value
     * @return mixed
     */
    private function resolve(array $value): mixed
    {
        $field = $this->createStub(Field::class);
        $field->method('getName')->willReturn('model');

        return (new ModelAttributeValue())->resolve(
            $field,
            null,
            $this->createStub(ResolveInfo::class),
            $value
        );
    }
}
