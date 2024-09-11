<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\Builder\Sort;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface
    as FieldNameResolver;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort\DefaultExpression;
use Magento\Framework\Search\RequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DefaultExpressionTest extends TestCase
{
    /**
     * @var FieldNameResolver|MockObject
     */
    private $fieldNameResolverMock;

    /**
     * @var DefaultExpression
     */
    private $defaultExpression;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fieldNameResolverMock = $this->createMock(FieldNameResolver::class);
        $this->defaultExpression = new DefaultExpression($this->fieldNameResolverMock);
    }

    /**
     * @dataProvider buildDataProvider
     * @param string $attributeCode
     * @param string $direction
     * @param bool $isSortable
     * @param bool $isFloatType
     * @param bool $isIntegerType
     * @param bool $isComplexType
     * @param array $expected
     * @return void
     */
    public function testBuild(
        string $attributeCode,
        string $direction,
        bool $isSortable,
        bool $isFloatType,
        bool $isIntegerType,
        bool $isComplexType,
        array $expected
    ): void {
        $requestMock = $this->createMock(RequestInterface::class);
        $attributeMock = $this->createMock(AttributeAdapter::class);
        $attributeMock->method('isSortable')
            ->willReturn($isSortable);
        $attributeMock->method('isFloatType')
            ->willReturn($isFloatType);
        $attributeMock->method('isIntegerType')
            ->willReturn($isIntegerType);
        $attributeMock->method('isComplexType')
            ->willReturn($isComplexType);
        $this->fieldNameResolverMock->method('getFieldName')
            ->willReturnCallback(
                function ($attribute, $context) use ($attributeCode) {
                    if (isset($attribute)) {
                        if (empty($context)) {
                            return $attributeCode;
                        } elseif ($context['type'] === 'sort') {
                            return 'sort_' . $attributeCode;
                        }
                    }
                }
            );

        $result = $this->defaultExpression->build($attributeMock, $direction, $requestMock);
        self::assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function buildDataProvider(): array
    {
        return [
            [
                'price',
                'desc',
                false,
                false,
                false,
                false,
                ['price' => ['order' => 'desc']],
            ],
            [
                'price',
                'desc',
                true,
                true,
                true,
                false,
                ['price' => ['order' => 'desc']],
            ],
            [
                'name',
                'desc',
                true,
                false,
                false,
                false,
                ['name.sort_name' => ['order' => 'desc']],
            ],
            [
                'not_eav_attribute',
                'desc',
                false,
                false,
                false,
                false,
                ['not_eav_attribute' => ['order' => 'desc']],
            ],
            [
                'color',
                'desc',
                true,
                false,
                false,
                true,
                ['color_value.sort_color' => ['order' => 'desc']]
            ]
        ];
    }
}
