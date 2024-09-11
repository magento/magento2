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
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort\Position;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter as FilterQuery;
use Magento\Framework\Search\Request\Query\MatchQuery;
use Magento\Framework\Search\RequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PositionTest extends TestCase
{
    /**
     * @var FieldNameResolver|MockObject
     */
    private $fieldNameResolverMock;

    /**
     * @var Position
     */
    private $position;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fieldNameResolverMock = $this->createMock(FieldNameResolver::class);
        $this->position = new Position($this->fieldNameResolverMock);
    }

    /**
     * @return void
     */
    public function testBuildWithoutCategoryIds(): void
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $queryMock = $this->createMock(BoolExpression::class);
        $requestMock->expects(self::atLeastOnce())
            ->method('getQuery')
            ->willReturn($queryMock);
        $queryMock->method('getType')
            ->willReturn(BoolExpression::TYPE_BOOL);
        $queryMock->method('getMust')
            ->willReturn([]);
        $attributeMock = $this->createMock(AttributeAdapter::class);
        $this->fieldNameResolverMock->expects(self::once())
            ->method('getFieldName')
            ->with($attributeMock)
            ->willReturn('position_category_2');

        $result = $this->position->build($attributeMock, 'desc', $requestMock);
        self::assertEquals(['position_category_2' => ['order' => 'desc']], $result);
    }

    /**
     * @return void
     */
    public function testBuildWithSingleCategoryId(): void
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $queryMock = $this->createMock(BoolExpression::class);
        $requestMock->expects(self::atLeastOnce())
            ->method('getQuery')
            ->willReturn($queryMock);
        $queryMock->method('getType')
            ->willReturn(BoolExpression::TYPE_BOOL);
        $filterQueryMock = $this->createMock(FilterQuery::class);
        $queryMock->method('getMust')
            ->willReturn(['category' => $filterQueryMock]);
        $matchQueryMock = $this->createMock(MatchQuery::class);
        $filterQueryMock->method('getReference')
            ->willReturn($matchQueryMock);
        $matchQueryMock->method('getValue')
            ->willReturn(5);
        $attributeMock = $this->createMock(AttributeAdapter::class);
        $this->fieldNameResolverMock->expects(self::once())
            ->method('getFieldName')
            ->with($attributeMock, ['categoryId' => 5])
            ->willReturn('position_category_5');

        $result = $this->position->build($attributeMock, 'desc', $requestMock);
        self::assertEquals(['position_category_5' => ['order' => 'desc']], $result);
    }

    /**
     * @return void
     */
    public function testBuildWithMultipleCategoryId(): void
    {
        $requestMock = $this->createMock(RequestInterface::class);
        $queryMock = $this->createMock(BoolExpression::class);
        $requestMock->expects(self::atLeastOnce())
            ->method('getQuery')
            ->willReturn($queryMock);
        $queryMock->method('getType')
            ->willReturn(BoolExpression::TYPE_BOOL);
        $filterQueryMock = $this->createMock(FilterQuery::class);
        $queryMock->method('getMust')
            ->willReturn(['category' => $filterQueryMock]);
        $matchQueryMock = $this->createMock(MatchQuery::class);
        $filterQueryMock->method('getReference')
            ->willReturn($matchQueryMock);
        $matchQueryMock->method('getValue')
            ->willReturn([7, 8]);
        $attributeMock = $this->createMock(AttributeAdapter::class);
        $matcher = self::exactly(2);
        $this->fieldNameResolverMock->expects($matcher)
            ->method('getFieldName')
            ->willReturnCallback(fn ($attribute, $context) => match ([$attribute, $context]) {
                [$attributeMock, ['categoryId' => 7]] => 'position_category_7',
                [$attributeMock, ['categoryId' => 8]] => 'position_category_8',
            });

        $result = $this->position->build($attributeMock, 'desc', $requestMock);
        self::assertArrayHasKey('_script', $result);
        self::assertArrayHasKey('script', $result['_script']);
        self::assertEquals(
            ['position_category_7', 'position_category_8'],
            $result['_script']['script']['params']['sortFieldNames']
        );
    }
}
