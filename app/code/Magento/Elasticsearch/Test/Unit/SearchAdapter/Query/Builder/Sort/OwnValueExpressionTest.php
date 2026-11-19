<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\Builder\Sort;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface
    as FieldNameResolver;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort\DefaultExpression;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort\ExpressionBuilder;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort\ExpressionBuilderInterface;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort\OwnValueExpression;
use Magento\Framework\Search\RequestInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OwnValueExpressionTest extends TestCase
{
    public function testSortableSourceAttributeIsSortedByItsOwnValueField(): void
    {
        $default = $this->createMock(ExpressionBuilderInterface::class);
        $default->expects(self::never())->method('build');
        $builder = new ExpressionBuilder($default, [], $this->createOwnValueExpression());

        $result = $builder->build(
            $this->createAttribute('color', true, true),
            'desc',
            $this->createMock(RequestInterface::class)
        );

        self::assertSame(
            [
                '_sort_color' => ['order' => 'desc', 'missing' => '_last', 'unmapped_type' => 'keyword'],
                'color_value.sort_color' => ['order' => 'desc'],
            ],
            $result
        );
    }

    #[DataProvider('defaultBuilderProvider')]
    public function testOtherAttributesUseTheDefaultBuilder(bool $isSortable, bool $isComplexType): void
    {
        $attribute = $this->createAttribute('name', $isSortable, $isComplexType);
        $request = $this->createMock(RequestInterface::class);
        $default = $this->createMock(ExpressionBuilderInterface::class);
        $default->expects(self::once())
            ->method('build')
            ->with($attribute, 'asc', $request)
            ->willReturn(['name.sort_name' => ['order' => 'asc']]);
        $builder = new ExpressionBuilder($default, [], $this->createOwnValueExpression());

        self::assertSame(['name.sort_name' => ['order' => 'asc']], $builder->build($attribute, 'asc', $request));
    }

    public static function defaultBuilderProvider(): array
    {
        return [
            'sortable, not a source attribute' => [true, false],
            'source attribute, not sortable' => [false, true],
        ];
    }

    public function testCustomBuilderStillWinsForSortableSourceAttribute(): void
    {
        $custom = $this->createMock(ExpressionBuilderInterface::class);
        $custom->expects(self::once())->method('build')->willReturn(['custom' => ['order' => 'asc']]);
        $builder = new ExpressionBuilder(
            $this->createMock(ExpressionBuilderInterface::class),
            ['color' => $custom],
            $this->createOwnValueExpression()
        );

        $result = $builder->build(
            $this->createAttribute('color', true, true),
            'asc',
            $this->createMock(RequestInterface::class)
        );

        self::assertSame(['custom' => ['order' => 'asc']], $result);
    }

    private function createOwnValueExpression(): OwnValueExpression
    {
        $fieldNameResolver = $this->createMock(FieldNameResolver::class);
        $fieldNameResolver->method('getFieldName')->willReturnCallback(
            fn (AttributeAdapter $attribute, array $context = []) => ($context['type'] ?? null) === 'sort'
                ? 'sort_' . $attribute->getAttributeCode()
                : $attribute->getAttributeCode()
        );

        return new OwnValueExpression(new DefaultExpression($fieldNameResolver));
    }

    private function createAttribute(string $code, bool $isSortable, bool $isComplexType): AttributeAdapter
    {
        $attribute = $this->createMock(AttributeAdapter::class);
        $attribute->method('getAttributeCode')->willReturn($code);
        $attribute->method('isSortable')->willReturn($isSortable);
        $attribute->method('isComplexType')->willReturn($isComplexType);

        return $attribute;
    }
}
