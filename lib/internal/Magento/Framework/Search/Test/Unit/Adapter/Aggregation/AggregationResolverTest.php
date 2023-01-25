<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Adapter\Aggregation;

use Magento\Framework\Search\Adapter\Aggregation\AggregationResolver;
use Magento\Framework\Search\Adapter\Aggregation\AggregationResolverInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AggregationResolverTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var AggregationResolverInterface|MockObject
     */
    private $specificAggregationResolver;

    /**
     * @var AggregationResolver
     */
    private $aggregationResolver;

    protected function setUp(): void
    {
        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->specificAggregationResolver = $this->getMockForAbstractClass(AggregationResolverInterface::class);

        $this->aggregationResolver = (new ObjectManager($this))->getObject(
            AggregationResolver::class,
            [
                'resolvers' => [
                    'specific_resolver' => $this->specificAggregationResolver,
                ],
            ]
        );
    }

    public function testResolve()
    {
        $documentIds = ['document_1', 'document_2'];
        $resolvedAggregations = ['aggregation_1'];

        $this->request->expects($this->atLeastOnce())->method('getIndex')->willReturn('specific_resolver');
        $this->specificAggregationResolver->expects($this->once())
            ->method('resolve')
            ->with($this->request, $documentIds)
            ->willReturn($resolvedAggregations);

        $this->assertEquals($resolvedAggregations, $this->aggregationResolver->resolve($this->request, $documentIds));
    }

    public function testResolveWithoutSpecificResolver()
    {
        $aggregations = ['aggregation_1'];

        $this->request->expects($this->atLeastOnce())->method('getIndex')->willReturn('index_1');
        $this->request->expects($this->once())->method('getAggregation')->willReturn($aggregations);

        $this->assertEquals($aggregations, $this->aggregationResolver->resolve($this->request, []));
    }
}
