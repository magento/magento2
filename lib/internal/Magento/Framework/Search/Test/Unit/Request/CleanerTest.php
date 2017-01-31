<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Request;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CleanerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\Search\Request\Aggregation\StatusInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $status;

    /**
     * @var \Magento\Framework\Search\Request\Cleaner
     */
    private $cleaner;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->status = $this->getMockBuilder('\Magento\Framework\Search\Request\Aggregation\StatusInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isEnabled'])
            ->getMockForAbstractClass();

        $this->cleaner = $helper->getObject(
            'Magento\Framework\Search\Request\Cleaner',
            ['aggregationStatus' => $this->status]
        );
    }

    public function testClean()
    {
        $this->status->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
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
            ->will($this->returnValue(false));
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
     * @expectedException \Exception
     * @expectedExceptionMessage Reference is not provided
     */
    public function testCleanFilteredQueryType()
    {
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid query type
     */
    public function testCleanQueryType()
    {
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid filter type
     */
    public function testCleanFilterType()
    {
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
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cycle found. Query filtered_query already used in request hierarchy
     */
    public function testCleanQueryCycle()
    {
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
     * @expectedException \Magento\Framework\Exception\StateException
     */
    public function testCleanFilterCycle()
    {
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
     * @expectedException \Exception
     * @expectedExceptionMessage Filter bool_filter does not exist
     */
    public function testCleanFilterNotFound()
    {
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
     * @expectedException \Exception
     * @expectedExceptionMessage Query test does not exist
     */
    public function testCleanQueryNotExists()
    {
        $requestData = [
            'query' => 'test',
            'queries' => [],
            'filters' => [],
        ];

        $this->cleaner->clean($requestData);
    }

    /**
     * @expectedException \Magento\Framework\Search\Request\EmptyRequestDataException
     * @expectedExceptionMessage Request query and filters are not set
     */
    public function testCleanEmptyQueryAndFilter()
    {
        $this->status->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
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
