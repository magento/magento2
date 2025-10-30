<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\Builder\Sort;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort\ExpressionBuilder;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort\ExpressionBuilderInterface;
use Magento\Framework\Search\RequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExpressionBuilderTest extends TestCase
{
    /**
     * @var ExpressionBuilderInterface|MockObject
     */
    private $defaultExpressionBuilderMock;

    /**
     * @var ExpressionBuilderInterface|MockObject
     */
    private $customExpressionBuilderMock;

    /**
     * @var string
     */
    private $customAttributeCode;

    /**
     * @var ExpressionBuilder
     */
    private $expressionBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->defaultExpressionBuilderMock = $this->createMock(ExpressionBuilderInterface::class);
        $this->customExpressionBuilderMock = $this->createMock(ExpressionBuilderInterface::class);
        $this->customAttributeCode = 'custom_attribute';
        $this->expressionBuilder = new ExpressionBuilder(
            $this->defaultExpressionBuilderMock,
            [$this->customAttributeCode => $this->customExpressionBuilderMock]
        );
    }

    public function testBuildUsingDefaultBuilder(): void
    {
        $direction = 'asc';
        $requestMock = $this->createMock(RequestInterface::class);
        $attributeMock = $this->createMock(AttributeAdapter::class);
        $attributeMock->method('getAttributeCode')
            ->willReturn('some_attribute');
        $sortExpression = ['some_attribute' => ['order' => 'asc']];
        $this->defaultExpressionBuilderMock->expects(self::once())
            ->method('build')
            ->with($attributeMock, $direction, $requestMock)
            ->willReturn($sortExpression);
        $this->customExpressionBuilderMock->expects(self::never())
            ->method('build');
        $result = $this->expressionBuilder->build($attributeMock, $direction, $requestMock);
        self::assertEquals($sortExpression, $result);
    }

    public function testBuildUsingCustomBuilder(): void
    {
        $direction = 'asc';
        $requestMock = $this->createMock(RequestInterface::class);
        $attributeMock = $this->createMock(AttributeAdapter::class);
        $attributeMock->method('getAttributeCode')
            ->willReturn($this->customAttributeCode);
        $sortExpression = [$this->customAttributeCode => ['order' => 'asc']];
        $this->customExpressionBuilderMock->expects(self::once())
            ->method('build')
            ->with($attributeMock, $direction, $requestMock)
            ->willReturn($sortExpression);
        $this->defaultExpressionBuilderMock->expects(self::never())
            ->method('build');
        $result = $this->expressionBuilder->build($attributeMock, $direction, $requestMock);
        self::assertEquals($sortExpression, $result);
    }
}
