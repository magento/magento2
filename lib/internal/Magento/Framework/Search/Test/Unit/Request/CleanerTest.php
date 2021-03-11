<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Test\Unit\Request;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CleanerTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \Magento\Framework\Search\Request\Aggregation\StatusInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $status;

    /**
     * @var \Magento\Framework\Search\Request\Cleaner
     */
    private $cleaner;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->status = $this->getMockBuilder(\Magento\Framework\Search\Request\Aggregation\StatusInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isEnabled'])
            ->getMockForAbstractClass();

        $this->cleaner = $helper->getObject(
            \Magento\Framework\Search\Request\Cleaner::class,
            ['aggregationStatus' => $this->status]
        );
    }

    public function testClean()
    {
        $this->status->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $requestData = [
            'query' => 'bool_query',
            'queries' => [
                'bool_query' => [
                    'queryReference' => [
                        ['ref' => 'bool_query_rm'],
                        ['ref' => 'match_query'],
                        ['ref' => 'filtered_query_to_filter2'],
                    ],
                    'type' => 'boolQuery',
                ],
                'match_query' => ['value' => 'ok', 'type' => 'matchQuery', 'is_bind' => true],
                'bool_query_rm' => [
                    'queryReference' => [
                        ['ref' => 'match_query_rm'],
                        ['ref' => 'filtered_query_to_query'],
                        ['ref' => 'filtered_query_to_filter'],
                    ],
                    'type' => 'boolQuery',
                ],
                'match_query_rm' => ['value' => '$some$', 'type' => 'matchQuery'],
                'match_query_rm2' => ['value' => '$some2$', 'type' => 'matchQuery'],
                'filtered_query_to_query' => [
                    'queryReference' => [['ref' => 'match_query_rm2']],
                    'type' => 'filteredQuery',
                ],
                'filtered_query_to_filter' => [
                    'filterReference' => [['ref' => 'bool_filter']],
                    'type' => 'filteredQuery',
                ],
                'filtered_query_to_filter2' => [
                    'filterReference' => [['ref' => 'bool_filter2']],
                    'type' => 'filteredQuery',
                ],
            ],
            'filters' => [
                'bool_filter' => [
                    'filterReference' => [['ref' => 'term_filter'], ['ref' => 'range_filter']],
                    'type' => 'boolFilter',
                ],
                'term_filter' => ['value' => '$val$', 'type' => 'termFilter'],
                'range_filter' => ['from' => '$from$', 'to' => '$to$', 'type' => 'rangeFilter'],
                'bool_filter2' => [
                    'filterReference' => [['ref' => 'term_filter2']],
                    'type' => 'boolFilter',
                ],
                'term_filter2' => ['value' => 'value_good', 'type' => 'termFilter', 'is_bind' => true],
            ],
        ];
        $exceptedRequestData = [
            'query' => 'bool_query',
            'queries' => [
                'bool_query' => [
                    'queryReference' => [['ref' => 'match_query'], ['ref' => 'filtered_query_to_filter2']],
                    'type' => 'boolQuery',
                ],
                'match_query' => ['value' => 'ok', 'type' => 'matchQuery', 'is_bind' => true],
                'filtered_query_to_filter2' => [
                    'filterReference' => [['ref' => 'bool_filter2']],
                    'type' => 'filteredQuery',
                ],
            ],
            'filters' => [
                'bool_filter2' => [
                    'filterReference' => [['ref' => 'term_filter2']],
                    'type' => 'boolFilter',
                ],
                'term_filter2' => ['value' => 'value_good', 'type' => 'termFilter', 'is_bind' => true],
            ],
        ];

        $result = $this->cleaner->clean($requestData);

        $this->assertEquals($exceptedRequestData, $result);
    }

    public function testCleanWithoutAggregations()
    {
        $this->status->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);
        $requestData = [
            'query' => 'bool_query',
            'queries' => [
                'bool_query' => [
                    'queryReference' => [
                        ['ref' => 'bool_query_rm'],
                        ['ref' => 'match_query'],
                        ['ref' => 'filtered_query_to_filter2'],
                    ],
                    'type' => 'boolQuery',
                ],
                'match_query' => ['value' => 'ok', 'type' => 'matchQuery', 'is_bind' => true],
                'bool_query_rm' => [
                    'queryReference' => [
                        ['ref' => 'match_query_rm'],
                        ['ref' => 'filtered_query_to_query'],
                        ['ref' => 'filtered_query_to_filter'],
                    ],
                    'type' => 'boolQuery',
                ],
                'match_query_rm' => ['value' => '$some$', 'type' => 'matchQuery'],
                'match_query_rm2' => ['value' => '$some2$', 'type' => 'matchQuery'],
                'filtered_query_to_query' => [
                    'queryReference' => [['ref' => 'match_query_rm2']],
                    'type' => 'filteredQuery',
                ],
                'filtered_query_to_filter' => [
                    'filterReference' => [['ref' => 'bool_filter']],
                    'type' => 'filteredQuery',
                ],
                'filtered_query_to_filter2' => [
                    'filterReference' => [['ref' => 'bool_filter2']],
                    'type' => 'filteredQuery',
                ],
            ],
            'filters' => [
                'bool_filter' => [
                    'filterReference' => [['ref' => 'term_filter'], ['ref' => 'range_filter']],
                    'type' => 'boolFilter',
                ],
                'term_filter' => ['value' => '$val$', 'type' => 'termFilter'],
                'range_filter' => ['from' => '$from$', 'to' => '$to$', 'type' => 'rangeFilter'],
                'bool_filter2' => [
                    'filterReference' => [['ref' => 'term_filter2']],
                    'type' => 'boolFilter',
                ],
                'term_filter2' => ['value' => 'value_good', 'type' => 'termFilter', 'is_bind' => true],
            ],
        ];
        $exceptedRequestData = [
            'query' => 'bool_query',
            'queries' => [
                'bool_query' => [
                    'queryReference' => [['ref' => 'match_query'], ['ref' => 'filtered_query_to_filter2']],
                    'type' => 'boolQuery',
                ],
                'match_query' => ['value' => 'ok', 'type' => 'matchQuery', 'is_bind' => true],
                'filtered_query_to_filter2' => [
                    'filterReference' => [['ref' => 'bool_filter2']],
                    'type' => 'filteredQuery',
                ],
            ],
            'filters' => [
                'bool_filter2' => [
                    'filterReference' => [['ref' => 'term_filter2']],
                    'type' => 'boolFilter',
                ],
                'term_filter2' => ['value' => 'value_good', 'type' => 'termFilter', 'is_bind' => true],
            ],
            'aggregations' => [],
        ];

        $result = $this->cleaner->clean($requestData);

        $this->assertEquals($exceptedRequestData, $result);
    }

    /**
     */
    public function testCleanFilteredQueryType()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Reference is not provided');

        $requestData = [
            'query' => 'filtered_query',
            'queries' => [
                'filtered_query' => [
                    'type' => 'filteredQuery',
                ],
            ],
            'filters' => [],
        ];

        $this->cleaner->clean($requestData);
    }

    /**
     */
    public function testCleanQueryType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid query type');

        $requestData = [
            'query' => 'filtered_query',
            'queries' => [
                'filtered_query' => [
                    'type' => 'fQuery',
                ],
            ],
            'filters' => [],
        ];

        $this->cleaner->clean($requestData);
    }

    /**
     */
    public function testCleanFilterType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid filter type');

        $requestData = [
            'query' => 'filtered_query',
            'queries' => [
                'filtered_query' => [
                    'filterReference' => [['ref' => 'filter']],
                    'type' => 'filteredQuery',
                ],
            ],
            'filters' => [
                'filter' => [
                    'type' => 'fType',
                ],
            ],
        ];

        $this->cleaner->clean($requestData);
    }

    /**
     */
    public function testCleanQueryCycle()
    {
        $this->expectException(\Magento\Framework\Exception\StateException::class);
        $this->expectExceptionMessage('A cycle was found. The "filtered_query" query is already used in the request hierarchy.');

        $requestData = [
            'query' => 'filtered_query',
            'queries' => [
                'filtered_query' => [
                    'queryReference' => [['ref' => 'filtered_query']],
                    'type' => 'boolQuery',
                ],
            ],
            'filters' => [],
        ];

        $this->cleaner->clean($requestData);
    }

    /**
     */
    public function testCleanFilterCycle()
    {
        $this->expectException(\Magento\Framework\Exception\StateException::class);

        $requestData = [
            'query' => 'filtered_query',
            'queries' => [
                'filtered_query' => [
                    'filterReference' => [['ref' => 'bool_filter']],
                    'type' => 'filteredQuery',
                ],
            ],
            'filters' => [
                'bool_filter' => [
                    'filterReference' => [['ref' => 'bool_filter']],
                    'type' => 'boolFilter',
                ],
            ],
        ];

        $this->cleaner->clean($requestData);
    }

    /**
     */
    public function testCleanFilterNotFound()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Filter bool_filter does not exist');

        $requestData = [
            'query' => 'filtered_query',
            'queries' => [
                'filtered_query' => [
                    'filterReference' => [['ref' => 'bool_filter']],
                    'type' => 'filteredQuery',
                ],
            ],
            'filters' => [],
        ];

        $this->cleaner->clean($requestData);
    }

    /**
     */
    public function testCleanQueryNotExists()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Query test does not exist');

        $requestData = [
            'query' => 'test',
            'queries' => [],
            'filters' => [],
        ];

        $this->cleaner->clean($requestData);
    }

    /**
     */
    public function testCleanEmptyQueryAndFilter()
    {
        $this->expectException(\Magento\Framework\Search\Request\EmptyRequestDataException::class);
        $this->expectExceptionMessage('The request query and filters aren\'t set. Verify the query and filters and try again.');

        $this->status->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $requestData = [
            'query' => 'bool_query',
            'queries' => [
                'bool_query' => [
                    'queryReference' => [
                        ['ref' => 'bool_query_rm'],
                        ['ref' => 'filtered_query_to_filter2'],
                    ],
                    'type' => 'boolQuery',
                ],
                'bool_query_rm' => [
                    'queryReference' => [
                        ['ref' => 'match_query_rm'],
                        ['ref' => 'filtered_query_to_query'],
                        ['ref' => 'filtered_query_to_filter'],
                    ],
                    'type' => 'boolQuery',
                ],
                'match_query_rm' => ['value' => '$some$', 'type' => 'matchQuery'],
                'match_query_rm2' => ['value' => '$some2$', 'type' => 'matchQuery'],
                'filtered_query_to_query' => [
                    'queryReference' => [['ref' => 'match_query_rm2']],
                    'type' => 'filteredQuery',
                ],
                'filtered_query_to_filter' => [
                    'filterReference' => [['ref' => 'bool_filter']],
                    'type' => 'filteredQuery',
                ],
                'filtered_query_to_filter2' => [
                    'filterReference' => [['ref' => 'bool_filter2']],
                    'type' => 'filteredQuery',
                ],
            ],
            'filters' => [
                'bool_filter' => [
                    'filterReference' => [['ref' => 'term_filter'], ['ref' => 'range_filter']],
                    'type' => 'boolFilter',
                ],
                'term_filter' => ['value' => '$val$', 'type' => 'termFilter'],
                'range_filter' => ['from' => '$from$', 'to' => '$to$', 'type' => 'rangeFilter'],
                'bool_filter2' => [
                    'filterReference' => [['ref' => 'term_filter2']],
                    'type' => 'boolFilter',
                ],
                'term_filter2' => ['value' => '$val$', 'type' => 'termFilter'],
            ],
        ];

        $this->cleaner->clean($requestData);
    }
}
