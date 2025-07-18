<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Request;

use Exception;
use InvalidArgumentException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Request\Aggregation\Metric;
use Magento\Framework\Search\Request\Aggregation\RangeBucket;
use Magento\Framework\Search\Request\Aggregation\TermBucket;
use Magento\Framework\Search\Request\Filter\Range;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Framework\Search\Request\Filter\Wildcard;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\Mapper;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\Request\Query\MatchQuery;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MapperTest extends TestCase
{
    private const ROOT_QUERY = 'someQuery';

    /**
     * @var ObjectManager
     */
    private $helper;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var MatchQuery|MockObject
     */
    private $queryMatch;

    /**
     * @var BoolExpression|MockObject
     */
    private $queryBool;

    /**
     * @var Filter|MockObject
     */
    private $queryFilter;

    /**
     * @var Term|MockObject
     */
    private $filterTerm;

    /**
     * @var Range|MockObject
     */
    private $filterRange;

    /**
     * @var BoolExpression|MockObject
     */
    private $filterBool;

    /**
     * @ingeritdoc
     */
    protected function setUp(): void
    {
        $this->helper = new ObjectManager($this);

        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->queryMatch = $this->getMockBuilder(MatchQuery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryBool = $this->getMockBuilder(BoolExpression::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryFilter = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterTerm = $this->getMockBuilder(Term::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterRange = $this->getMockBuilder(Range::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterBool = $this->getMockBuilder(BoolExpression::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param $queries
     *
     * @return void
     * @dataProvider getQueryMatchProvider
     */
    public function testGetQueryMatch($queries): void
    {
        $query = $queries[self::ROOT_QUERY];
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with(
                MatchQuery::class,
                [
                    'name' => $query['name'],
                    'value' => $query['value'],
                    'boost' => $query['boost'] ?? 1,
                    'matches' => $query['match'],
                ]
            )->willReturn($this->queryMatch);

        /** @var Mapper $mapper */
        $mapper = $this->helper->getObject(
            Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => $queries,
                'rootQueryName' => self::ROOT_QUERY,
                'aggregation' => [],
                'filters' => []
            ]
        );

        $this->assertEquals($this->queryMatch, $mapper->getRootQuery());
    }

    /**
     * @return void
     */
    public function testGetQueryNotUsedStateException(): void
    {
        $this->expectException(StateException::class);
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_MATCH,
                'name' => 'someName',
                'value' => 'someValue',
                'boost' => 3,
                'match' => 'someMatches'
            ],
            'notUsedQuery' => [
                'type' => QueryInterface::TYPE_MATCH,
                'name' => 'someName',
                'value' => 'someValue',
                'boost' => 3,
                'match' => 'someMatches'
            ]
        ];
        $query = $queries['someQuery'];
        $this->objectManager->expects($this->once())->method('create')
            ->with(
                MatchQuery::class,
                [
                    'name' => $query['name'],
                    'value' => $query['value'],
                    'boost' => $query['boost'] ?? 1,
                    'matches' => $query['match'],
                ]
            )
            ->willReturn($this->queryMatch);

        /** @var Mapper $mapper */
        $mapper = $this->helper->getObject(
            Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => $queries,
                'rootQueryName' => self::ROOT_QUERY,
                'aggregation' => [],
                'filters' => []
            ]
        );

        $this->assertEquals($this->queryMatch, $mapper->getRootQuery());
    }

    /**
     * @return void
     */
    public function testGetQueryUsedStateException(): void
    {
        $this->expectException(StateException::class);
        /** @var Mapper $mapper */
        $mapper = $this->helper->getObject(
            Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => [
                    self::ROOT_QUERY => [
                        'type' => QueryInterface::TYPE_BOOL,
                        'name' => 'someName',
                        'queryReference' => [
                            [
                                'clause' => 'someClause',
                                'ref' => 'someQuery'
                            ],
                        ],
                    ],
                ],
                'rootQueryName' => self::ROOT_QUERY,
                'aggregation' => [],
                'filters' => []
            ]
        );

        $this->assertEquals($this->queryMatch, $mapper->getRootQuery());
    }

    /**
     * @param $queries
     *
     * @return void
     * @dataProvider getQueryFilterQueryReferenceProvider
     */
    public function testGetQueryFilterQueryReference($queries): void
    {
        $query = $queries['someQueryMatch'];
        $queryRoot = $queries[self::ROOT_QUERY];
        $this->objectManager
            ->method('create')
            ->willReturnCallback(function ($arg1, $arg2) use ($query, $queryRoot) {

                if ($arg1 == MatchQuery::class &&
                        $arg2['name'] == $query['name'] &&
                        $arg2['value'] == $query['value'] &&
                        $arg2['boost'] == 1 &&
                        $arg2['matches'] == 'someMatches') {
                    return $this->queryMatch;
                } elseif ($arg1 == Filter::class &&
                        $arg2['name'] == $queryRoot['name'] &&
                        $arg2['reference'] == $this->queryMatch &&
                        $arg2['referenceType'] == Filter::REFERENCE_QUERY) {
                    return $this->queryFilter;
                }
            });

        /** @var Mapper $mapper */
        $mapper = $this->helper->getObject(
            Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => $queries,
                'rootQueryName' => self::ROOT_QUERY,
                'aggregation' => [],
                'filters' => []
            ]
        );

        $this->assertEquals($this->queryFilter, $mapper->getRootQuery());
    }

    public function testGetQueryFilterReferenceException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Reference is not provided');
        /** @var Mapper $mapper */
        $mapper = $this->helper->getObject(
            Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => [
                    'someQuery' => [
                        'type' => QueryInterface::TYPE_FILTER,
                    ]
                ],
                'rootQueryName' => self::ROOT_QUERY,
                'aggregation' => [],
                'filters' => []
            ]
        );

        $mapper->getRootQuery();
    }

    /**
     * @param $queries
     * @dataProvider getQueryBoolProvider
     */
    public function testGetQueryBool($queries): void
    {
        $query = $queries['someQueryMatch'];
        $rootQueries = $queries[self::ROOT_QUERY];
        $this->objectManager
            ->method('create')
            ->willReturnCallback(function ($arg1, $arg2) use ($query, $rootQueries) {
                if ($arg1 == MatchQuery::class &&
                        $arg2['name'] == $query['name'] &&
                        $arg2['value'] == $query['value'] &&
                        $arg2['boost'] == 1 &&
                        $arg2['matches'] == 'someMatches') {
                    return $this->queryMatch;
                }
                if ($arg1 == BoolExpression::class &&
                        $arg2['name'] == $rootQueries['name'] &&
                        $arg2['boost'] == ($rootQueries['boost'] ?? 1) &&
                        $arg2['someClause']['someQueryMatch'] == $this->queryMatch) {
                    return $this->queryBool;
                }
            });

        /** @var Mapper $mapper */
        $mapper = $this->helper->getObject(
            Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => $queries,
                'rootQueryName' => self::ROOT_QUERY,
                'aggregation' => [],
                'filters' => []
            ]
        );

        $this->assertEquals($this->queryBool, $mapper->getRootQuery());
    }

    /**
     * @return void
     */
    public function testGetQueryInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @var Mapper $mapper */
        $mapper = $this->helper->getObject(
            Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => [
                    self::ROOT_QUERY => [
                        'type' => 'invalid_type'
                    ]
                ],
                'rootQueryName' => self::ROOT_QUERY,
                'aggregation' => [],
                'filters' => []
            ]
        );

        $mapper->getRootQuery();
    }

    /**
     * @return void
     */
    public function testGetQueryException(): void
    {
        $this->expectException(Exception::class);
        /** @var Mapper $mapper */
        $mapper = $this->helper->getObject(
            Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => [],
                'rootQueryName' => self::ROOT_QUERY,
                'filters' => []
            ]
        );

        $mapper->getRootQuery();
    }

    /**
     * @return void
     */
    public function testGetFilterTerm(): void
    {
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_FILTER,
                'name' => 'someName',
                'filterReference' => [
                    [
                        'ref' => 'someFilter'
                    ]
                ]
            ]
        ];
        $filters = [
            'someFilter' => [
                'type' => FilterInterface::TYPE_TERM,
                'name' => 'someName',
                'field' => 'someField',
                'value' => 'someValue'
            ]
        ];

        $filter = $filters['someFilter'];
        $query = $queries[self::ROOT_QUERY];
        $this->objectManager
            ->method('create')
            ->willReturnCallback(function ($arg1, $arg2) use ($filter, $query) {
                if ($arg1 == Term::class &&
                    $arg2['name'] == $filter['name'] &&
                    $arg2['field'] == $filter['field'] &&
                    $arg2['value'] == $filter['value']) {
                    return $this->filterTerm;
                } elseif ($arg1 == Filter::class &&
                    $arg2['name'] == $query['name'] &&
                    $arg2['boost'] == 1 &&
                    $arg2['reference'] == $this->filterTerm &&
                    $arg2['referenceType'] == Filter::REFERENCE_FILTER) {
                    return $this->queryFilter;
                }
            });

        /** @var Mapper $mapper */
        $mapper = $this->helper->getObject(
            Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => $queries,
                'rootQueryName' => self::ROOT_QUERY,
                'aggregation' => [],
                'filters' => $filters
            ]
        );

        $this->assertEquals($this->queryFilter, $mapper->getRootQuery());
    }

    /**
     * @return void
     */
    public function testGetFilterWildcard(): void
    {
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_FILTER,
                'name' => 'someName',
                'filterReference' => [
                    [
                        'ref' => 'someFilter'
                    ]
                ]
            ]
        ];
        $filters = [
            'someFilter' => [
                'type' => FilterInterface::TYPE_WILDCARD,
                'name' => 'someName',
                'field' => 'someField',
                'value' => 'someValue'
            ]
        ];

        $filter = $filters['someFilter'];
        $query = $queries[self::ROOT_QUERY];
        $this->objectManager
            ->method('create')
            ->willReturnCallback(function ($arg1, $arg2) use ($filter, $query) {
                if ($arg1 == Wildcard::class &&
                    $arg2['name'] == $filter['name'] &&
                    $arg2['field'] == $filter['field'] &&
                    $arg2['value'] == $filter['value']) {
                    return $this->filterTerm;
                } elseif ($arg1 == Filter::class &&
                    $arg2['name'] == $query['name'] &&
                    $arg2['boost'] == 1 &&
                    $arg2['reference'] == $this->filterTerm &&
                    $arg2['referenceType'] == Filter::REFERENCE_FILTER) {
                    return $this->queryFilter;
                }
            });

        /** @var Mapper $mapper */
        $mapper = $this->helper->getObject(
            Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => $queries,
                'rootQueryName' => self::ROOT_QUERY,
                'aggregation' => [],
                'filters' => $filters
            ]
        );

        $this->assertEquals($this->queryFilter, $mapper->getRootQuery());
    }

    /**
     * @return void
     */
    public function testGetFilterRange(): void
    {
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_FILTER,
                'name' => 'someName',
                'filterReference' => [
                    [
                        'ref' => 'someFilter'
                    ]
                ]
            ]
        ];
        $filters = [
            'someFilter' => [
                'type' => FilterInterface::TYPE_RANGE,
                'name' => 'someName',
                'field' => 'someField',
                'from' => 'from',
                'to' => 'to'
            ]
        ];

        $filter = $filters['someFilter'];
        $query = $queries[self::ROOT_QUERY];
        $this->objectManager
            ->method('create')
            ->willReturnCallback(function ($arg1, $arg2) use ($filter, $query) {
                if ($arg1 == Range::class &&
                    $arg2['name'] == $filter['name'] &&
                    $arg2['field'] == $filter['field'] &&
                    $arg2['from'] == $filter['from'] &&
                    $arg2['to'] == $filter['to']) {
                    return $this->filterRange;
                } elseif ($arg1 == Filter::class &&
                    $arg2['name'] == $query['name'] &&
                    $arg2['boost'] == 1 &&
                    $arg2['reference'] == $this->filterRange &&
                    $arg2['referenceType'] == Filter::REFERENCE_FILTER) {
                    return $this->queryFilter;
                }
            });

        /** @var Mapper $mapper */
        $mapper = $this->helper->getObject(
            Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => $queries,
                'rootQueryName' => self::ROOT_QUERY,
                'aggregation' => [],
                'filters' => $filters
            ]
        );

        $this->assertEquals($this->queryFilter, $mapper->getRootQuery());
    }

    /**
     * @return void
     */
    public function testGetFilterBool(): void
    {
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_FILTER,
                'name' => 'someName',
                'filterReference' => [
                    [
                        'ref' => 'someFilter'
                    ]
                ]
            ]
        ];
        $filters = [
            'someFilter' => [
                'type' => FilterInterface::TYPE_BOOL,
                'name' => 'someName',
                'filterReference' => [
                    [
                        'ref' => 'someFilterTerm',
                        'clause' => 'someClause'
                    ]
                ]
            ],
            'someFilterTerm' => [
                'type' => FilterInterface::TYPE_TERM,
                'name' => 'someName',
                'field' => 'someField',
                'value' => 'someValue'
            ]
        ];

        $someFilterTerm = $filters['someFilterTerm'];
        $someFilter = $filters['someFilter'];
        $query = $queries[self::ROOT_QUERY];

        $this->objectManager
            ->method('create')
            ->willReturnCallback(function ($arg1, $arg2) use ($someFilterTerm, $someFilter, $query) {
                if ($arg1 == Term::class &&
                    $arg2['name'] == $someFilterTerm['name'] &&
                    $arg2['field'] == $someFilterTerm['field'] &&
                    $arg2['value'] == $someFilterTerm['value']) {
                    return $this->filterTerm;
                } elseif ($arg1 == \Magento\Framework\Search\Request\Filter\BoolExpression::class &&
                    $arg2['name'] == $someFilter['name'] &&
                    $arg2['someClause']['someFilterTerm'] == $this->filterTerm) {
                    return $this->filterBool;
                } elseif ($arg1 == Filter::class &&
                    $arg2['name'] == $query['name'] &&
                    $arg2['boost'] == 1 &&
                    $arg2['reference'] == $this->filterBool &&
                    $arg2['referenceType'] == Filter::REFERENCE_FILTER) {
                    return $this->queryFilter;
                }
            });

        /** @var Mapper $mapper */
        $mapper = $this->helper->getObject(
            Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => $queries,
                'rootQueryName' => self::ROOT_QUERY,
                'aggregation' => [],
                'filters' => $filters
            ]
        );

        $this->assertEquals($this->queryFilter, $mapper->getRootQuery());
    }

    /**
     * @return void
     */
    public function testGetFilterNotUsedStateException(): void
    {
        $this->expectException(StateException::class);
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_FILTER,
                'name' => 'someName',
                'filterReference' => [
                    [
                        'ref' => 'someFilter'
                    ]
                ]
            ]
        ];
        $filters = [
            'someFilter' => [
                'type' => FilterInterface::TYPE_TERM,
                'name' => 'someName',
                'field' => 'someField',
                'value' => 'someValue'
            ],
            'notUsedFilter' => [
                'type' => FilterInterface::TYPE_TERM,
                'name' => 'someName',
                'field' => 'someField',
                'value' => 'someValue'
            ]
        ];

        $filter = $filters['someFilter'];
        $query = $queries[self::ROOT_QUERY];
        $this->objectManager
            ->method('create')
            ->willReturnCallback(function ($arg1, $arg2) use ($filter, $query) {
                if ($arg1 == Term::class &&
                        $arg2['name'] == $filter['name'] &&
                        $arg2['field'] == $filter['field'] &&
                        $arg2['value'] == $filter['value']) {
                    return $this->filterTerm;
                } elseif ($arg1 == Filter::class &&
                        $arg2['name'] == $query['name'] &&
                        $arg2['boost'] == 1 &&
                        $arg2['reference'] == $this->filterTerm &&
                        $arg2['referenceType'] == Filter::REFERENCE_FILTER) {
                    return $this->queryFilter;

                }
            });

        /** @var Mapper $mapper */
        $mapper = $this->helper->getObject(
            Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => $queries,
                'rootQueryName' => self::ROOT_QUERY,
                'aggregation' => [],
                'filters' => $filters
            ]
        );

        $this->assertEquals($this->queryFilter, $mapper->getRootQuery());
    }

    /**
     * @return void
     */
    public function testGetFilterUsedStateException(): void
    {
        $this->expectException(StateException::class);
        /** @var Mapper $mapper */
        $mapper = $this->helper->getObject(
            Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => [
                    self::ROOT_QUERY => [
                        'type' => QueryInterface::TYPE_FILTER,
                        'name' => 'someName',
                        'filterReference' => [
                            [
                                'ref' => 'someFilter'
                            ]
                        ]
                    ]
                ],
                'rootQueryName' => self::ROOT_QUERY,
                'filters' => [
                    'someFilter' => [
                        'type' => FilterInterface::TYPE_BOOL,
                        'name' => 'someName',
                        'filterReference' => [
                            [
                                'ref' => 'someFilter',
                                'clause' => 'someClause'
                            ]
                        ]
                    ]
                ],
                'aggregation' => []
            ]
        );

        $this->assertEquals($this->queryMatch, $mapper->getRootQuery());
    }

    /**
     * @return void
     */
    public function testGetFilterInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid filter type');
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_FILTER,
                'name' => 'someName',
                'filterReference' => [
                    [
                        'ref' => 'someFilter'
                    ]
                ]
            ]
        ];
        $filters = [
            'someFilter' => [
                'type' => 'invalid_type'
            ]
        ];

        /** @var Mapper $mapper */
        $mapper = $this->helper->getObject(
            Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => $queries,
                'rootQueryName' => self::ROOT_QUERY,
                'aggregation' => [],
                'filters' => $filters
            ]
        );

        $this->assertEquals($this->queryFilter, $mapper->getRootQuery());
    }

    /**
     * @return void
     */
    public function testGetFilterException(): void
    {
        $this->expectException(Exception::class);
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_FILTER,
                'name' => 'someName',
                'boost' => 3,
                'filterReference' => [
                    [
                        'ref' => 'someQueryMatch',
                        'clause' => 'someClause'
                    ]
                ]
            ]
        ];

        /** @var Mapper $mapper */
        $mapper = $this->helper->getObject(
            Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => $queries,
                'rootQueryName' => self::ROOT_QUERY,
                'filters' => []
            ]
        );

        $this->assertEquals($this->queryBool, $mapper->getRootQuery());
    }

    /**
     * @return array
     */
    public static function getQueryMatchProvider(): array
    {
        return [
            [
                [
                    self::ROOT_QUERY => [
                        'type' => QueryInterface::TYPE_MATCH,
                        'name' => 'someName',
                        'value' => 'someValue',
                        'boost' => 3,
                        'match' => 'someMatches'
                    ]
                ]
            ],
            [
                [
                    self::ROOT_QUERY => [
                        'type' => QueryInterface::TYPE_MATCH,
                        'name' => 'someName',
                        'value' => 'someValue',
                        'match' => 'someMatches'
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public static function getQueryFilterQueryReferenceProvider(): array
    {
        return [
            [
                [
                    self::ROOT_QUERY => [
                        'type' => QueryInterface::TYPE_FILTER,
                        'name' => 'someName',
                        'boost' => 3,
                        'queryReference' => [
                            [
                                'ref' => 'someQueryMatch',
                                'clause' => 'someClause'
                            ]
                        ]
                    ],
                    'someQueryMatch' => [
                        'type' => QueryInterface::TYPE_MATCH,
                        'value' => 'someValue',
                        'name' => 'someName',
                        'match' => 'someMatches'
                    ]
                ]
            ],
            [
                [
                    self::ROOT_QUERY => [
                        'type' => QueryInterface::TYPE_FILTER,
                        'name' => 'someName',
                        'queryReference' => [
                            [
                                'ref' => 'someQueryMatch',
                                'clause' => 'someClause'
                            ]
                        ]
                    ],
                    'someQueryMatch' => [
                        'type' => QueryInterface::TYPE_MATCH,
                        'value' => 'someValue',
                        'name' => 'someName',
                        'match' => 'someMatches'
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public static function getQueryBoolProvider(): array
    {
        return [
            [
                [
                    self::ROOT_QUERY => [
                        'type' => QueryInterface::TYPE_BOOL,
                        'name' => 'someName',
                        'boost' => 3,
                        'queryReference' => [
                            [
                                'ref' => 'someQueryMatch',
                                'clause' => 'someClause'
                            ]
                        ]
                    ],
                    'someQueryMatch' => [
                        'type' => QueryInterface::TYPE_MATCH,
                        'value' => 'someValue',
                        'name' => 'someName',
                        'match' => 'someMatches'
                    ]
                ]
            ],
            [
                [
                    self::ROOT_QUERY => [
                        'type' => QueryInterface::TYPE_BOOL,
                        'name' => 'someName',
                        'queryReference' => [
                            [
                                'ref' => 'someQueryMatch',
                                'clause' => 'someClause'
                            ]
                        ]
                    ],
                    'someQueryMatch' => [
                        'type' => QueryInterface::TYPE_MATCH,
                        'value' => 'someValue',
                        'name' => 'someName',
                        'match' => 'someMatches'
                    ]
                ]
            ]
        ];
    }

    /**
     * @return void
     */
    public function testGetBucketsInvalidBucket(): void
    {
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_MATCH,
                'value' => 'someValue',
                'name' => 'someName',
                'match' => 'someMatches'
            ]
        ];
        $bucket = [
            "name" => "price_bucket",
            "field" => "price",
            "method" => "test",
            "type" => "invalidBucket"
        ];

        /** @var Mapper $mapper */
        $mapper = $this->helper->getObject(
            Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => $queries,
                'rootQueryName' => self::ROOT_QUERY,
                'aggregations' => [$bucket]
            ]
        );

        $this->expectException(StateException::class);
        $this->expectExceptionMessage('The bucket type is invalid. Verify and try again.');
        $mapper->getBuckets();
    }
}
