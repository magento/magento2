<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Filter;

use Magento\Elasticsearch\SearchAdapter\Filter\Builder;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Range;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Term;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Wildcard;
use Magento\Framework\Search\Request\Filter\BoolExpression;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @var Builder
     */
    protected $model;

    /**
     * @var Range|MockObject
     */
    protected $range;

    /**
     * @var Term|MockObject
     */
    protected $term;

    /**
     * @var Wildcard|MockObject
     */
    protected $wildcard;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->range = $this->getMockBuilder(Range::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->term = $this->getMockBuilder(Term::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->wildcard = $this->getMockBuilder(Wildcard::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->term->expects($this->any())
            ->method('buildFilter')
            ->willReturn([]);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            Builder::class,
            [
                'range' => $this->range,
                'term' => $this->term,
                'wildcard' => $this->wildcard
            ]
        );
    }

    /**
     * Test build() method failure
     */
    public function testBuildFailure()
    {
        $this->expectException(\InvalidArgumentException::class);

        $filter = $this->getMockBuilder(FilterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $filter->expects($this->any())
            ->method('getType')
            ->willReturn('unknown');

        $this->model->build($filter, 'must');
    }

    /**
     * Test build() method
     * @param string $filterMock
     * @param string $filterType
     * @dataProvider buildDataProvider
     */
    public function testBuild($filterMock, $filterType)
    {
        $filter = $this->getMockBuilder($filterMock)
            ->setMethods(['getMust', 'getType', 'getShould', 'getMustNot'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $filter->expects($this->any())
            ->method('getType')
            ->willReturn($filterType);
        $childFilter = $this->getMockBuilder(FilterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $childFilter->expects($this->any())
            ->method('getType')
            ->willReturn('termFilter');
        $filter->expects($this->any())
            ->method('getMust')
            ->willReturn([$childFilter]);
        $filter->expects($this->any())
            ->method('getShould')
            ->willReturn([$childFilter]);
        $filter->expects($this->any())
            ->method('getMustNot')
            ->willReturn([$childFilter]);

        $result = $this->model->build($filter, 'must');
        $this->assertNotNull($result);
    }

    /**
     * Test build() method with negation
     * @param string $filterMock
     * @param string $filterType
     * @dataProvider buildDataProvider
     */
    public function testBuildNegation($filterMock, $filterType)
    {
        $filter = $this->getMockBuilder($filterMock)
            ->setMethods(['getMust', 'getType', 'getShould', 'getMustNot'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $filter->expects($this->any())
            ->method('getType')
            ->willReturn($filterType);
        $childFilter = $this->getMockBuilder(FilterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $childFilter->expects($this->any())
            ->method('getType')
            ->willReturn('termFilter');
        $filter->expects($this->any())
            ->method('getMust')
            ->willReturn([$childFilter]);
        $filter->expects($this->any())
            ->method('getShould')
            ->willReturn([$childFilter]);
        $filter->expects($this->any())
            ->method('getMustNot')
            ->willReturn([$childFilter]);

        $result = $this->model->build($filter, 'must_not');
        $this->assertNotNull($result);
    }

    /**
     * @return array
     */
    public function buildDataProvider()
    {
        return [
            [FilterInterface::class,
                'termFilter'
            ],
            [BoolExpression::class,
                'boolFilter'
            ],
        ];
    }
}
