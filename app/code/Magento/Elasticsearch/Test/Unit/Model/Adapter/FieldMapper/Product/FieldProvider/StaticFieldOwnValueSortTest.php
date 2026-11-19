<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeFieldsMappingProcessorInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ConverterInterface
    as IndexTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ResolverInterface
    as FieldIndexResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface
    as FieldNameResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface
    as FieldTypeConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface
    as FieldTypeResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\StaticField;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StaticFieldOwnValueSortTest extends TestCase
{
    #[DataProvider('attributeProvider')]
    public function testOwnValueSortFieldIsMappedForSortableSourceAttributesOnly(
        bool $isSortable,
        bool $isComplexType,
        bool $expectOwnValueSortField
    ): void {
        $attribute = $this->createMock(AttributeAdapter::class);
        $attribute->method('getAttributeCode')->willReturn('color');
        $attribute->method('isSortable')->willReturn($isSortable);
        $attribute->method('isComplexType')->willReturn($isComplexType);
        $attributeProvider = $this->createMock(AttributeProvider::class);
        $attributeProvider->method('getByAttributeCode')->willReturn($attribute);

        $fieldNameResolver = $this->createMock(FieldNameResolver::class);
        $fieldNameResolver->method('getFieldName')->willReturnCallback(
            fn ($attribute, $context = []) => match ($context['type'] ?? null) {
                'sort' => 'sort_color',
                'text' => 'color_value',
                default => 'color',
            }
        );
        $fieldTypeConverter = $this->createMock(FieldTypeConverterInterface::class);
        $fieldTypeConverter->method('convert')->willReturnArgument(0);
        $indexTypeConverter = $this->createMock(IndexTypeConverterInterface::class);
        $indexTypeConverter->method('convert')->willReturn(false);
        $fieldTypeResolver = $this->createMock(FieldTypeResolver::class);
        $fieldTypeResolver->method('getFieldType')->willReturn('integer');
        $processor = $this->createMock(AttributeFieldsMappingProcessorInterface::class);
        $processor->method('process')->willReturnArgument(1);

        $staticField = new StaticField(
            $this->createMock(Config::class),
            $fieldTypeConverter,
            $indexTypeConverter,
            $fieldTypeResolver,
            $this->createMock(FieldIndexResolver::class),
            $attributeProvider,
            $fieldNameResolver,
            [],
            $processor
        );
        $eavAttribute = $this->createMock(AbstractAttribute::class);
        $eavAttribute->method('getAttributeCode')->willReturn('color');

        $mapping = $staticField->getField($eavAttribute);

        if ($expectOwnValueSortField) {
            self::assertSame(
                ['type' => 'keyword', 'index' => false, 'normalizer' => 'folding'],
                $mapping['_sort_color']
            );
        } else {
            self::assertArrayNotHasKey('_sort_color', $mapping);
        }
    }

    public static function attributeProvider(): array
    {
        return [
            'sortable source attribute' => [true, true, true],
            'source attribute, not sortable' => [false, true, false],
            'sortable, not a source attribute' => [true, false, false],
        ];
    }
}
