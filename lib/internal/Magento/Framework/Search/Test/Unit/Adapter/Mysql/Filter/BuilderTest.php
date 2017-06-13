<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql\Filter;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Filter\PreprocessorInterface;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\Query\BoolExpression as RequestBoolQuery;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Filter\Builder
     */
    private $builder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PreprocessorInterface
     */
    private $preprocessor;

    /**
     * @var ConditionManager|\PHPUnit_Framework_MockObject_MockObject $conditionManager
     */
    private $conditionManager;

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->conditionManager = $this->getMockBuilder(\Magento\Framework\Search\Adapter\Mysql\ConditionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateCondition', 'combineQueries', 'wrapBrackets'])
            ->getMock();
        $this->conditionManager->expects($this->any())
            ->method('generateCondition')
            ->will(
                $this->returnCallback(
                    function ($field, $operator, $value) {
                        return sprintf('%s %s %s', $field, $operator, $value);
                    }
                )
            );
        $this->conditionManager->expects($this->any())
            ->method('combineQueries')
            ->will(
                $this->returnCallback(
                    function ($queries, $operator) {
                        return implode(
                            ' ' . $operator . ' ',
                            array_filter($queries, 'strlen')
                        );
                    }
                )
            );
        $this->conditionManager->expects($this->any())
            ->method('wrapBrackets')
            ->will(
                $this->returnCallback(
                    function ($query) {
                        return !empty($query) ? sprintf('(%s)', $query) : '';
                    }
                )
            );

        $rangeBuilder = $this->getMockBuilder(\Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Range::class)
            ->setMethods(['buildFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $rangeBuilder->expects($this->any())
            ->method('buildFilter')
            ->will(
                $this->returnCallback(
                    function (FilterInterface $filter, $isNegation) {
                        /**
                         * @var \Magento\Framework\Search\Request\Filter\Range $filter
                         * @var \Magento\Framework\DB\Adapter\AdapterInterface $adapter
                         */
                        $fromCondition = '';
                        if ($filter->getFrom() !== null) {
                            $fromCondition = $this->conditionManager->generateCondition(
                                $filter->getField(),
                                ($isNegation ? '<' : '>='),
                                $filter->getFrom()
                            );
                        }
                        $toCondition = '';
                        if ($filter->getTo() !== null) {
                            $toCondition = $this->conditionManager->generateCondition(
                                $filter->getField(),
                                ($isNegation ? '>=' : '<'),
                                $filter->getTo()
                            );
                        }
                        $unionOperator = $isNegation ? Select::SQL_OR : Select::SQL_AND;

                        return $this->conditionManager->combineQueries([$fromCondition, $toCondition], $unionOperator);
                    }
                )
            );

        $termBuilder = $this->getMockBuilder(\Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Term::class)
            ->setMethods(['buildFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $termBuilder->expects($this->any())
            ->method('buildFilter')
            ->will(
                $this->returnCallback(
                    function (FilterInterface $filter, $isNegation) {
                        /**
                         * @var \Magento\Framework\Search\Request\Filter\Term $filter
                         * @var \Magento\Framework\DB\Adapter\AdapterInterface $adapter
                         */
                        return $this->conditionManager->generateCondition(
                            $filter->getField(),
                            ($isNegation ? '!=' : '='),
                            $filter->getValue()
                        );
                    }
                )
            );

        $this->preprocessor = $this->getMockBuilder(
            \Magento\Framework\Search\Adapter\Mysql\Filter\PreprocessorInterface::class
        )
            ->setMethods(['process'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->preprocessor->expects($this->any())->method('process')->willReturnCallback(
            function ($filter, $isNegation, $queryString) {
                return $this->conditionManager->wrapBrackets($queryString);
            }
        );

        $this->builder = $objectManager->getObject(
            \Magento\Framework\Search\Adapter\Mysql\Filter\Builder::class,
            [
                'range' => $rangeBuilder,
                'term' => $termBuilder,
                'conditionManager' => $this->conditionManager,
                'preprocessor' => $this->preprocessor
            ]
        );
    }

    /**
     * @param FilterInterface|\PHPUnit_Framework_MockObject_MockObject $filter
     * @param string $conditionType
     * @param string $expectedResult
     * @dataProvider buildFilterDataProvider
     */
    public function testBuildFilter($filter, $conditionType, $expectedResult)
    {
        $actualResult = $this->builder->build($filter, $conditionType);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function buildFilterDataProvider()
    {
        return array_merge(
            $this->buildTermFilterDataProvider(),
            $this->buildRangeFilterDataProvider(),
            $this->buildBoolFilterDataProvider()
        );
    }

    /**
     * @return array
     */
    public function buildTermFilterDataProvider()
    {
        return [
            'termFilter' => [
                'filter' => $this->createTermFilter('term1', 123),
                'conditionType' => RequestBoolQuery::QUERY_CONDITION_MUST,
                'expectedResult' => '(term1 = 123)',
            ],
            'termFilterNegative' => [
                'filter' => $this->createTermFilter('term1', 123),
                'conditionType' => RequestBoolQuery::QUERY_CONDITION_NOT,
                'expectedResult' => '(term1 != 123)',
            ],
        ];
    }

    /**
     * @param $field
     * @param $value
     * @return \Magento\Framework\Search\Request\Filter\BoolExpression|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTermFilter($field, $value)
    {
        $filter = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\Term::class)
            ->setMethods(['getField', 'getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $filter->expects($this->exactly(1))
            ->method('getField')
            ->will($this->returnValue($field));
        $filter->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($value));
        return $filter;
    }

    /**
     * Data provider for BuildFilter
     *
     * @return array
     */
    public function buildRangeFilterDataProvider()
    {
        return [
            'rangeFilter' => [
                'filter' => $this->createRangeFilter('range1', 0, 10),
                'conditionType' => RequestBoolQuery::QUERY_CONDITION_MUST,
                'expectedResult' => '(range1 >= 0 AND range1 < 10)',
            ],
            'rangeFilterNegative' => [
                'filter' => $this->createRangeFilter('range1', 0, 10),
                'conditionType' => RequestBoolQuery::QUERY_CONDITION_NOT,
                'expectedResult' => '(range1 < 0 OR range1 >= 10)',
            ]

        ];
    }

    /**
     * @param $field
     * @param $from
     * @param $to
     * @return \Magento\Framework\Search\Request\Filter\BoolExpression|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createRangeFilter($field, $from, $to)
    {
        $filter = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\Range::class)
            ->setMethods(['getField', 'getFrom', 'getTo'])
            ->disableOriginalConstructor()
            ->getMock();

        $filter->expects($this->exactly(2))
            ->method('getField')
            ->will($this->returnValue($field));
        $filter->expects($this->atLeastOnce())
            ->method('getFrom')
            ->will($this->returnValue($from));
        $filter->expects($this->atLeastOnce())
            ->method('getTo')
            ->will($this->returnValue($to));
        return $filter;
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function buildBoolFilterDataProvider()
    {
        return [
            'boolFilterWithMust' => [
                'filter' => $this->createBoolFilter(
                    [ //must
                        $this->createTermFilter('term1', 1),
                        $this->createRangeFilter('range1', 0, 10),
                    ],
                    [], //should
                    [] // mustNot
                ),
                'conditionType' => RequestBoolQuery::QUERY_CONDITION_MUST,
                'expectedResult' => '(((term1 = 1)) AND ((range1 >= 0 AND range1 < 10)))',
            ],
            'boolFilterWithShould' => [
                'filter' => $this->createBoolFilter(
                    [], //must
                    [ //should
                        $this->createTermFilter('term1', 1),
                        $this->createRangeFilter('range1', 0, 10),
                    ],
                    [] // mustNot
                ),
                'conditionType' => RequestBoolQuery::QUERY_CONDITION_MUST,
                'expectedResult' => '((((term1 = 1)) OR ((range1 >= 0 AND range1 < 10))))',
            ],
            'boolFilterWithMustNot' => [
                'filter' => $this->createBoolFilter(
                    [], //must
                    [], //should
                    [ // mustNot
                        $this->createTermFilter('term1', 1),
                        $this->createRangeFilter('range1', 0, 10),
                    ]
                ),
                'conditionType' => RequestBoolQuery::QUERY_CONDITION_MUST,
                'expectedResult' => '((((term1 != 1)) AND ((range1 < 0 OR range1 >= 10))))',
            ],
            'boolFilterWithAllFields' => [
                'filter' => $this->createBoolFilter(
                    [ //must
                        $this->createTermFilter('term1', 1),
                        $this->createRangeFilter('range1', 0, 10),
                    ],
                    [ //should
                        $this->createTermFilter('term2', 1),
                        $this->createRangeFilter('range2', 0, 10),
                    ],
                    [ // mustNot
                        $this->createTermFilter('term3', 1),
                        $this->createRangeFilter('range3', 0, 10),
                    ]
                ),
                'conditionType' => RequestBoolQuery::QUERY_CONDITION_MUST,
                'expectedResult' => '(((term1 = 1)) AND ((range1 >= 0 AND range1 < 10))'
                    . ' AND (((term2 = 1)) OR ((range2 >= 0 AND range2 < 10)))'
                    . ' AND (((term3 != 1)) AND ((range3 < 0 OR range3 >= 10))))'
            ],
            'boolFilterInBoolFilter' => [
                'filter' => $this->createBoolFilter(
                    [ //must
                        $this->createTermFilter('term1', 1),
                        $this->createRangeFilter('range1', 0, 10),
                    ],
                    [ //should
                        $this->createTermFilter('term2', 1),
                        $this->createRangeFilter('range2', 0, 10),
                    ],
                    [ // mustNot
                        $this->createTermFilter('term3', 1),
                        $this->createRangeFilter('range3', 0, 10),
                        $this->createBoolFilter(
                            [ //must
                                $this->createTermFilter('term4', 1),
                                $this->createRangeFilter('range4', 0, 10),
                            ],
                            [ //should
                                $this->createTermFilter('term5', 1),
                                $this->createRangeFilter('range5', 0, 10),
                            ],
                            [ // mustNot
                                $this->createTermFilter('term6', 1),
                                $this->createRangeFilter('range6', 0, 10),
                            ]
                        ),
                    ]
                ),
                'conditionType' => RequestBoolQuery::QUERY_CONDITION_MUST,
                'expectedResult' => '(((term1 = 1)) AND ((range1 >= 0 AND range1 < 10))'
                    . ' AND (((term2 = 1)) OR ((range2 >= 0 AND range2 < 10)))'
                    . ' AND (((term3 != 1)) AND ((range3 < 0 OR range3 >= 10))'
                    . ' AND ((((term4 != 1)) AND ((range4 < 0 OR range4 >= 10))'
                    . ' AND (((term5 != 1)) OR ((range5 < 0 OR range5 >= 10)))'
                    . ' AND (((term6 = 1)) AND ((range6 >= 0 AND range6 < 10)))))))',
            ],
            'boolEmpty' => [
                'filter' => $this->createBoolFilter([], [], []),
                'conditionType' => RequestBoolQuery::QUERY_CONDITION_MUST,
                'expectedResult' => '',
            ]
        ];
    }

    /**
     * @param array $must
     * @param array $should
     * @param array $mustNot
     * @return \Magento\Framework\Search\Request\Filter\BoolExpression|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createBoolFilter(array $must, array $should, array $mustNot)
    {
        $filter = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\BoolExpression::class)
            ->setMethods(['getMust', 'getShould', 'getMustNot'])
            ->disableOriginalConstructor()
            ->getMock();

        $filter->expects($this->once())
            ->method('getMust')
            ->will($this->returnValue($must));
        $filter->expects($this->once())
            ->method('getShould')
            ->will($this->returnValue($should));
        $filter->expects($this->once())
            ->method('getMustNot')
            ->will($this->returnValue($mustNot));
        return $filter;
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUnknownFilterType()
    {
        /** @var FilterInterface|\PHPUnit_Framework_MockObject_MockObject $filter */
        $filter = $this->getMockBuilder(\Magento\Framework\Search\Request\FilterInterface::class)
            ->setMethods(['getType'])
            ->getMockForAbstractClass();
        $filter->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('unknownType'));
        $this->builder->build($filter, RequestBoolQuery::QUERY_CONDITION_MUST);
    }
}
