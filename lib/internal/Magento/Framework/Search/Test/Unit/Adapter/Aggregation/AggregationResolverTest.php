<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Adapter\Aggregation;

use Magento\Framework\Search\Adapter\Aggregation\AggregationResolver;
use Magento\Framework\Search\Adapter\Aggregation\AggregationResolverInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AggregationResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var AggregationResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $specificAggregationResolver;

    /**
     * @var AggregationResolver
     */
    private $aggregationResolver;

    protected function setUp()
    {
        $this->request = $this->createMock(RequestInterface::class);
        $this->specificAggregationResolver = $this->createMock(AggregationResolverInterface::class);

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
