<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit\Request;

use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MapperTest extends \PHPUnit_Framework_TestCase
{
    const ROOT_QUERY = 'someQuery';

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Search\Request\Query\Match|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryMatch;

    /**
     * @var \Magento\Framework\Search\Request\Query\BoolExpression|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryBool;

    /**
     * @var \Magento\Framework\Search\Request\Query\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryFilter;

    /**
     * @var \Magento\Framework\Search\Request\Filter\Term|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterTerm;

    /**
     * @var \Magento\Framework\Search\Request\Filter\Wildcard|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterWildcard;

    /**
     * @var \Magento\Framework\Search\Request\Filter\Range|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterRange;

    /**
     * @var \Magento\Framework\Search\Request\Filter\Bool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBool;

    protected function setUp()
    {
        $this->helper = new ObjectManager($this);

        $this->objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->queryMatch = $this->getMockBuilder(\Magento\Framework\Search\Request\Query\Match::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryBool = $this->getMockBuilder(\Magento\Framework\Search\Request\Query\BoolExpression::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryFilter = $this->getMockBuilder(\Magento\Framework\Search\Request\Query\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterTerm = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\Term::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterRange = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\Range::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterBool = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\BoolExpression::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterWildcard = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\Wildcard::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param $queries
     * @dataProvider getQueryMatchProvider
     */
    public function testGetQueryMatch($queries)
    {
        $query = $queries[self::ROOT_QUERY];
        $this->objectManager->expects($this->once())->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Request\Query\Match::class),
                $this->equalTo(
                    [
                        'name' => $query['name'],
                        'value' => $query['value'],
                        'boost' => isset($query['boost']) ? $query['boost'] : 1,
                        'matches' => $query['match'],
                    ]
                )
            )
            ->will($this->returnValue($this->queryMatch));

        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
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
     * @expectedException \Magento\Framework\Exception\StateException
     */
    public function testGetQueryNotUsedStateException()
    {
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_MATCH,
                'name' => 'someName',
                'value' => 'someValue',
                'boost' => 3,
                'match' => 'someMatches',
            ],
            'notUsedQuery' => [
                'type' => QueryInterface::TYPE_MATCH,
                'name' => 'someName',
                'value' => 'someValue',
                'boost' => 3,
                'match' => 'someMatches',
            ],
        ];
        $query = $queries['someQuery'];
        $this->objectManager->expects($this->once())->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Request\Query\Match::class),
                $this->equalTo(
                    [
                        'name' => $query['name'],
                        'value' => $query['value'],
                        'boost' => isset($query['boost']) ? $query['boost'] : 1,
                        'matches' => $query['match'],
                    ]
                )
            )
            ->will($this->returnValue($this->queryMatch));

        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
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
     * @expectedException \Magento\Framework\Exception\StateException
     */
    public function testGetQueryUsedStateException()
    {
        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => [
                    self::ROOT_QUERY => [
                        'type' => QueryInterface::TYPE_BOOL,
                        'name' => 'someName',
                        'queryReference' => [
                            [
                                'clause' => 'someClause',
                                'ref' => 'someQuery',
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
     * @dataProvider getQueryFilterQueryReferenceProvider
     */
    public function testGetQueryFilterQueryReference($queries)
    {
        $query = $queries['someQueryMatch'];
        $this->objectManager->expects($this->at(0))->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Request\Query\Match::class),
                $this->equalTo(
                    [
                        'name' => $query['name'],
                        'value' => $query['value'],
                        'boost' => 1,
                        'matches' => 'someMatches',
                    ]
                )
            )
            ->will($this->returnValue($this->queryMatch));
        $query = $queries[self::ROOT_QUERY];
        $this->objectManager->expects($this->at(1))->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Request\Query\Filter::class),
                $this->equalTo(
                    [
                        'name' => $query['name'],
                        'boost' => isset($query['boost']) ? $query['boost'] : 1,
                        'reference' => $this->queryMatch,
                        'referenceType' => Filter::REFERENCE_QUERY,
                    ]
                )
            )
            ->will($this->returnValue($this->queryFilter));

        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
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

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Reference is not provided
     */
    public function testGetQueryFilterReferenceException()
    {
        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => [
                    'someQuery' => [
                        'type' => QueryInterface::TYPE_FILTER,
                    ],
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
    public function testGetQueryBool($queries)
    {
        $query = $queries['someQueryMatch'];
        $this->objectManager->expects($this->at(0))->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Request\Query\Match::class),
                $this->equalTo(
                    [
                        'name' => $query['name'],
                        'value' => $query['value'],
                        'boost' => 1,
                        'matches' => 'someMatches',
                    ]
                )
            )
            ->will($this->returnValue($this->queryMatch));
        $query = $queries[self::ROOT_QUERY];
        $this->objectManager->expects($this->at(1))->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Request\Query\BoolExpression::class),
                $this->equalTo(
                    [
                        'name' => $query['name'],
                        'boost' => isset($query['boost']) ? $query['boost'] : 1,
                        'someClause' => ['someQueryMatch' => $this->queryMatch],
                    ]
                )
            )
            ->will($this->returnValue($this->queryBool));

        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
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
     * #@expectedException \InvalidArgumentException
     */
    public function testGetQueryInvalidArgumentException()
    {
        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => [
                    self::ROOT_QUERY => [
                        'type' => 'invalid_type',
                    ],
                ],
                'rootQueryName' => self::ROOT_QUERY,
                'aggregation' => [],
                'filters' => []
            ]
        );

        $mapper->getRootQuery();
    }

    /**
     * @expectedException \Exception
     */
    public function testGetQueryException()
    {
        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => [],
                'rootQueryName' => self::ROOT_QUERY,
                'filters' => []
            ]
        );

        $mapper->getRootQuery();
    }

    public function testGetFilterTerm()
    {
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_FILTER,
                'name' => 'someName',
                'filterReference' => [
                    [
                        'ref' => 'someFilter',
                    ],
                ],
            ],
        ];
        $filters = [
            'someFilter' => [
                'type' => FilterInterface::TYPE_TERM,
                'name' => 'someName',
                'field' => 'someField',
                'value' => 'someValue',
            ],
        ];

        $filter = $filters['someFilter'];
        $this->objectManager->expects($this->at(0))->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Request\Filter\Term::class),
                $this->equalTo(
                    [
                        'name' => $filter['name'],
                        'field' => $filter['field'],
                        'value' => $filter['value'],
                    ]
                )
            )
            ->will($this->returnValue($this->filterTerm));
        $query = $queries[self::ROOT_QUERY];
        $this->objectManager->expects($this->at(1))->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Request\Query\Filter::class),
                $this->equalTo(
                    [
                        'name' => $query['name'],
                        'boost' => 1,
                        'reference' => $this->filterTerm,
                        'referenceType' => Filter::REFERENCE_FILTER,
                    ]
                )
            )
            ->will($this->returnValue($this->queryFilter));

        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
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

    public function testGetFilterWildcard()
    {
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_FILTER,
                'name' => 'someName',
                'filterReference' => [
                    [
                        'ref' => 'someFilter',
                    ],
                ],
            ],
        ];
        $filters = [
            'someFilter' => [
                'type' => FilterInterface::TYPE_WILDCARD,
                'name' => 'someName',
                'field' => 'someField',
                'value' => 'someValue',
            ],
        ];

        $filter = $filters['someFilter'];
        $this->objectManager->expects($this->at(0))->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Request\Filter\Wildcard::class),
                $this->equalTo(
                    [
                        'name' => $filter['name'],
                        'field' => $filter['field'],
                        'value' => $filter['value'],
                    ]
                )
            )
            ->will($this->returnValue($this->filterTerm));
        $query = $queries[self::ROOT_QUERY];
        $this->objectManager->expects($this->at(1))->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Request\Query\Filter::class),
                $this->equalTo(
                    [
                        'name' => $query['name'],
                        'boost' => 1,
                        'reference' => $this->filterTerm,
                        'referenceType' => Filter::REFERENCE_FILTER,
                    ]
                )
            )
            ->will($this->returnValue($this->queryFilter));

        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
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

    public function testGetFilterRange()
    {
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_FILTER,
                'name' => 'someName',
                'filterReference' => [
                    [
                        'ref' => 'someFilter',
                    ],
                ],
            ],
        ];
        $filters = [
            'someFilter' => [
                'type' => FilterInterface::TYPE_RANGE,
                'name' => 'someName',
                'field' => 'someField',
                'from' => 'from',
                'to' => 'to',
            ],
        ];

        $filter = $filters['someFilter'];
        $this->objectManager->expects($this->at(0))->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Request\Filter\Range::class),
                $this->equalTo(
                    [
                        'name' => $filter['name'],
                        'field' => $filter['field'],
                        'from' => $filter['from'],
                        'to' => $filter['to'],
                    ]
                )
            )
            ->will($this->returnValue($this->filterRange));
        $query = $queries[self::ROOT_QUERY];
        $this->objectManager->expects($this->at(1))->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Request\Query\Filter::class),
                $this->equalTo(
                    [
                        'name' => $query['name'],
                        'boost' => 1,
                        'reference' => $this->filterRange,
                        'referenceType' => Filter::REFERENCE_FILTER,
                    ]
                )
            )
            ->will($this->returnValue($this->queryFilter));

        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
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

    public function testGetFilterBool()
    {
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_FILTER,
                'name' => 'someName',
                'filterReference' => [
                    [
                        'ref' => 'someFilter',
                    ],
                ],
            ],
        ];
        $filters = [
            'someFilter' => [
                'type' => FilterInterface::TYPE_BOOL,
                'name' => 'someName',
                'filterReference' => [
                    [
                        'ref' => 'someFilterTerm',
                        'clause' => 'someClause',
                    ],
                ],
            ],
            'someFilterTerm' => [
                'type' => FilterInterface::TYPE_TERM,
                'name' => 'someName',
                'field' => 'someField',
                'value' => 'someValue',
            ],
        ];

        $filter = $filters['someFilterTerm'];
        $this->objectManager->expects($this->at(0))->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Request\Filter\Term::class),
                $this->equalTo(
                    [
                        'name' => $filter['name'],
                        'field' => $filter['field'],
                        'value' => $filter['value'],
                    ]
                )
            )
            ->will($this->returnValue($this->filterTerm));
        $filter = $filters['someFilter'];
        $this->objectManager->expects($this->at(1))->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Request\Filter\BoolExpression::class),
                $this->equalTo(
                    [
                        'name' => $filter['name'],
                        'someClause' => ['someFilterTerm' => $this->filterTerm],
                    ]
                )
            )
            ->will($this->returnValue($this->filterBool));
        $query = $queries[self::ROOT_QUERY];
        $this->objectManager->expects($this->at(2))->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Request\Query\Filter::class),
                $this->equalTo(
                    [
                        'name' => $query['name'],
                        'boost' => 1,
                        'reference' => $this->filterBool,
                        'referenceType' => Filter::REFERENCE_FILTER,
                    ]
                )
            )
            ->will($this->returnValue($this->queryFilter));

        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
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
     * @expectedException \Magento\Framework\Exception\StateException
     */
    public function testGetFilterNotUsedStateException()
    {
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_FILTER,
                'name' => 'someName',
                'filterReference' => [
                    [
                        'ref' => 'someFilter',
                    ],
                ],
            ],
        ];
        $filters = [
            'someFilter' => [
                'type' => FilterInterface::TYPE_TERM,
                'name' => 'someName',
                'field' => 'someField',
                'value' => 'someValue',
            ],
            'notUsedFilter' => [
                'type' => FilterInterface::TYPE_TERM,
                'name' => 'someName',
                'field' => 'someField',
                'value' => 'someValue',
            ],
        ];

        $filter = $filters['someFilter'];
        $this->objectManager->expects($this->at(0))->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Request\Filter\Term::class),
                $this->equalTo(
                    [
                        'name' => $filter['name'],
                        'field' => $filter['field'],
                        'value' => $filter['value'],
                    ]
                )
            )
            ->will($this->returnValue($this->filterTerm));
        $query = $queries[self::ROOT_QUERY];
        $this->objectManager->expects($this->at(1))->method('create')
            ->with(
                $this->equalTo(\Magento\Framework\Search\Request\Query\Filter::class),
                $this->equalTo(
                    [
                        'name' => $query['name'],
                        'boost' => 1,
                        'reference' => $this->filterTerm,
                        'referenceType' => Filter::REFERENCE_FILTER,
                    ]
                )
            )
            ->will($this->returnValue($this->queryFilter));

        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
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
     * @expectedException \Magento\Framework\Exception\StateException
     */
    public function testGetFilterUsedStateException()
    {
        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => [
                    self::ROOT_QUERY => [
                        'type' => QueryInterface::TYPE_FILTER,
                        'name' => 'someName',
                        'filterReference' => [
                            [
                                'ref' => 'someFilter',
                            ],
                        ],
                    ],
                ],
                'rootQueryName' => self::ROOT_QUERY,
                'filters' => [
                    'someFilter' => [
                        'type' => FilterInterface::TYPE_BOOL,
                        'name' => 'someName',
                        'filterReference' => [
                            [
                                'ref' => 'someFilter',
                                'clause' => 'someClause',
                            ],
                        ],
                    ],
                ],
                'aggregation' => [],
            ]
        );

        $this->assertEquals($this->queryMatch, $mapper->getRootQuery());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid filter type
     */
    public function testGetFilterInvalidArgumentException()
    {
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_FILTER,
                'name' => 'someName',
                'filterReference' => [
                    [
                        'ref' => 'someFilter',
                    ],
                ],
            ],
        ];
        $filters = [
            'someFilter' => [
                'type' => 'invalid_type',
            ],
        ];

        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
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
     * @expectedException \Exception
     */
    public function testGetFilterException()
    {
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_FILTER,
                'name' => 'someName',
                'boost' => 3,
                'filterReference' => [
                    [
                        'ref' => 'someQueryMatch',
                        'clause' => 'someClause',
                    ],
                ],
            ],
        ];

        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => $queries,
                'rootQueryName' => self::ROOT_QUERY,
                'filters' => []
            ]
        );

        $this->assertEquals($this->queryBool, $mapper->getRootQuery());
    }

    public function getQueryMatchProvider()
    {
        return [
            [
                [
                    self::ROOT_QUERY => [
                        'type' => QueryInterface::TYPE_MATCH,
                        'name' => 'someName',
                        'value' => 'someValue',
                        'boost' => 3,
                        'match' => 'someMatches',
                    ],
                ],
            ],
            [
                [
                    self::ROOT_QUERY => [
                        'type' => QueryInterface::TYPE_MATCH,
                        'name' => 'someName',
                        'value' => 'someValue',
                        'match' => 'someMatches',
                    ],
                ]
            ]
        ];
    }

    public function getQueryFilterQueryReferenceProvider()
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
                                'clause' => 'someClause',
                            ],
                        ],
                    ],
                    'someQueryMatch' => [
                        'type' => QueryInterface::TYPE_MATCH,
                        'value' => 'someValue',
                        'name' => 'someName',
                        'match' => 'someMatches',
                    ],
                ],
            ],
            [
                [
                    self::ROOT_QUERY => [
                        'type' => QueryInterface::TYPE_FILTER,
                        'name' => 'someName',
                        'queryReference' => [
                            [
                                'ref' => 'someQueryMatch',
                                'clause' => 'someClause',
                            ],
                        ],
                    ],
                    'someQueryMatch' => [
                        'type' => QueryInterface::TYPE_MATCH,
                        'value' => 'someValue',
                        'name' => 'someName',
                        'match' => 'someMatches',
                    ],
                ]
            ]
        ];
    }

    public function getQueryBoolProvider()
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
                                'clause' => 'someClause',
                            ],
                        ],
                    ],
                    'someQueryMatch' => [
                        'type' => QueryInterface::TYPE_MATCH,
                        'value' => 'someValue',
                        'name' => 'someName',
                        'match' => 'someMatches',
                    ],
                ],
            ],
            [
                [
                    self::ROOT_QUERY => [
                        'type' => QueryInterface::TYPE_BOOL,
                        'name' => 'someName',
                        'queryReference' => [
                            [
                                'ref' => 'someQueryMatch',
                                'clause' => 'someClause',
                            ],
                        ],
                    ],
                    'someQueryMatch' => [
                        'type' => QueryInterface::TYPE_MATCH,
                        'value' => 'someValue',
                        'name' => 'someName',
                        'match' => 'someMatches',
                    ],
                ]
            ]
        ];
    }

    public function testGetBucketsTermBucket()
    {
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_MATCH,
                'value' => 'someValue',
                'name' => 'someName',
                'match' => 'someMatches',
            ],
        ];

        $bucket = [
            "name" => "category_bucket",
            "field" => "category",
            "metric" => [
                ["type" => "sum"],
                ["type" => "count"],
                ["type" => "min"],
                ["type" => "max"],
            ],
            "type" => "termBucket",
        ];
        $metricClass = \Magento\Framework\Search\Request\Aggregation\Metric::class;
        $bucketClass = \Magento\Framework\Search\Request\Aggregation\TermBucket::class;
        $queryClass = \Magento\Framework\Search\Request\Query\Match::class;
        $queryArguments = [
            'name' => $queries[self::ROOT_QUERY]['name'],
            'value' => $queries[self::ROOT_QUERY]['value'],
            'boost' => 1,
            'matches' => $queries[self::ROOT_QUERY]['match'],
        ];
        $arguments = [
            'name' => $bucket['name'],
            'field' => $bucket['field'],
            'metrics' => [null, null, null, null],
        ];
        $this->objectManager->expects($this->any())->method('create')
            ->withConsecutive(
                [$this->equalTo($queryClass), $this->equalTo($queryArguments)],
                [$this->equalTo($metricClass), $this->equalTo(['type' => $bucket['metric'][0]['type']])],
                [$this->equalTo($metricClass), $this->equalTo(['type' => $bucket['metric'][1]['type']])],
                [$this->equalTo($metricClass), $this->equalTo(['type' => $bucket['metric'][2]['type']])],
                [$this->equalTo($metricClass), $this->equalTo(['type' => $bucket['metric'][3]['type']])],
                [$this->equalTo($bucketClass), $this->equalTo($arguments)]
            )
            ->will($this->returnValue(null));

        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => $queries,
                'rootQueryName' => self::ROOT_QUERY,
                'aggregation' => [$bucket]
            ]
        );
        $mapper->getBuckets();
    }

    public function testGetBucketsRangeBucket()
    {
        $queries = [
            self::ROOT_QUERY => [
                'type' => QueryInterface::TYPE_MATCH,
                'value' => 'someValue',
                'name' => 'someName',
                'match' => 'someMatches',
            ],
        ];

        $bucket = [
            "name" => "price_bucket",
            "field" => "price",
            "metric" => [
                ["type" => "sum"],
                ["type" => "count"],
                ["type" => "min"],
                ["type" => "max"],
            ],
            "range" => [
                ["from" => "", "to" => "50"],
                ["from" => "50", "to" => "100"],
                ["from" => "100", "to" => ""],
            ],
            "type" => "rangeBucket",
        ];
        $metricClass = \Magento\Framework\Search\Request\Aggregation\Metric::class;
        $bucketClass = \Magento\Framework\Search\Request\Aggregation\RangeBucket::class;
        $rangeClass = \Magento\Framework\Search\Request\Aggregation\Range::class;
        $queryClass = \Magento\Framework\Search\Request\Query\Match::class;
        $queryArguments = [
            'name' => $queries[self::ROOT_QUERY]['name'],
            'value' => $queries[self::ROOT_QUERY]['value'],
            'boost' => 1,
            'matches' => $queries[self::ROOT_QUERY]['match'],
        ];
        $arguments = [
            'name' => $bucket['name'],
            'field' => $bucket['field'],
            'metrics' => [null, null, null, null],
            'ranges' => [null, null, null],
        ];
        $this->objectManager->expects($this->any())->method('create')
            ->withConsecutive(
                [$this->equalTo($queryClass), $this->equalTo($queryArguments)],
                [$this->equalTo($metricClass), $this->equalTo(['type' => $bucket['metric'][0]['type']])],
                [$this->equalTo($metricClass), $this->equalTo(['type' => $bucket['metric'][1]['type']])],
                [$this->equalTo($metricClass), $this->equalTo(['type' => $bucket['metric'][2]['type']])],
                [$this->equalTo($metricClass), $this->equalTo(['type' => $bucket['metric'][3]['type']])],
                [
                    $this->equalTo($rangeClass),
                    $this->equalTo(['from' => $bucket['range'][0]['from'], 'to' => $bucket['range'][0]['to']])
                ],
                [
                    $this->equalTo($rangeClass),
                    $this->equalTo(['from' => $bucket['range'][1]['from'], 'to' => $bucket['range'][1]['to']])
                ],
                [
                    $this->equalTo($rangeClass),
                    $this->equalTo(['from' => $bucket['range'][2]['from'], 'to' => $bucket['range'][2]['to']])
                ],
                [
                    $this->equalTo($bucketClass),
                    $this->equalTo($arguments)
                ]
            )
            ->will($this->returnValue(null));

        /** @var \Magento\Framework\Search\Request\Mapper $mapper */
        $mapper = $this->helper->getObject(
            \Magento\Framework\Search\Request\Mapper::class,
            [
                'objectManager' => $this->objectManager,
                'queries' => $queries,
                'rootQueryName' => self::ROOT_QUERY,
                'aggregation' => [$bucket]
            ]
        );
        $mapper->getBuckets();
    }
}
