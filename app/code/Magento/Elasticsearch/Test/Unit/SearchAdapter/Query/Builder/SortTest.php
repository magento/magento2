<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort\ExpressionBuilderInterface as SortExpressionBuilder;
use Magento\Framework\Search\Request;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SortTest extends TestCase
{
    /**
     * @var AttributeProvider|MockObject
     */
    private $attributeAdapterProviderMock;

    /**
     * @var SortExpressionBuilder|MockObject
     */
    private $sortExpressionBuilderMock;

    /**
     * @var Sort
     */
    private $sortBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->attributeAdapterProviderMock = $this->createMock(AttributeProvider::class);
        $this->sortExpressionBuilderMock = $this->createMock(SortExpressionBuilder::class);
        $this->sortBuilder = new Sort($this->attributeAdapterProviderMock, $this->sortExpressionBuilderMock);
    }

    /**
     * @return void
     */
    public function testGetSort(): void
    {
        $request = $this->createMock(Request::class);
        $sortItems = [
            [
                'field' => 'some_attribute',
                'direction' => 'DESC',
            ]
        ];
        $request->expects(self::once())
            ->method('getSort')
            ->willReturn($sortItems);
        $attributeAdapterMock = $this->createMock(AttributeAdapter::class);
        $this->attributeAdapterProviderMock->expects(self::once())
            ->method('getByAttributeCode')
            ->with('some_attribute')
            ->willReturn($attributeAdapterMock);
        $sortExpression = ['some_attribute' => ['order' => 'desc']];
        $this->sortExpressionBuilderMock->expects(self::once())
            ->method('build')
            ->with($attributeAdapterMock, 'desc', $request)
            ->willReturn($sortExpression);

        self::assertEquals(
            [$sortExpression],
            $this->sortBuilder->getSort($request)
        );
    }
}
