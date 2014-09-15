<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Search\Adapter\Mysql\Filter;

use Magento\TestFramework\Helper\ObjectManager;

class BuilderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;
    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Filter\Builder
     */
    private $builder;

    /**
     * Set up
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->adapter = $adapter = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->setMethods(['quote'])
            ->getMockForAbstractClass();
        $this->adapter->expects($this->any())
            ->method('quote')
            ->will($this->returnArgument(0));

        $rangeBuilder = $this->getMockBuilder('\Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Range')
            ->setMethods(['buildFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $rangeBuilder->expects($this->any())
            ->method('buildFilter')
            ->will(
                $this->returnCallback(
                    function (\Magento\Framework\Search\Request\FilterInterface $filter) use ($adapter) {
                        /**
                         * @var \Magento\Framework\Search\Request\Filter\Range $filter
                         * @var \Magento\Framework\DB\Adapter\AdapterInterface $adapter
                         */
                        return sprintf(
                            '%s >= %s AND %s < %s',
                            $filter->getField(),
                            $adapter->quote($filter->getFrom()),
                            $filter->getField(),
                            $adapter->quote($filter->getTo())
                        );
                    }
                )
            );

        $termBuilder = $this->getMockBuilder('\Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Term')
            ->setMethods(['buildFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $termBuilder->expects($this->any())
            ->method('buildFilter')
            ->will(
                $this->returnCallback(
                    function (\Magento\Framework\Search\Request\FilterInterface $filter) use ($adapter) {
                        /**
                         * @var \Magento\Framework\Search\Request\Filter\Term $filter
                         * @var \Magento\Framework\DB\Adapter\AdapterInterface $adapter
                         */
                        return sprintf(
                            '%s = %s',
                            $filter->getField(),
                            $adapter->quote($filter->getValue())
                        );
                    }
                )
            );

        $this->builder = $objectManager->getObject(
            'Magento\Framework\Search\Adapter\Mysql\Filter\Builder',
            [
                'range' => $rangeBuilder,
                'term' => $termBuilder,
            ]
        );
    }

    /**
     * @param \Magento\Framework\Search\Request\FilterInterface|\PHPUnit_Framework_MockObject_MockObject $filter
     * @param string $expectedResult
     * @dataProvider buildFilterDataProvider
     */
    public function testBuildFilter($filter, $expectedResult)
    {
        $actualResult = $this->builder->build($filter);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function buildFilterDataProvider()
    {
        return array_merge(
            $this->buildTermFilterDataProvider(),
            $this->buildRangeFilterDataProvider(),
            $this->buildBoolFilterDataProvider()
        );
    }

    /**
     * Data provider for BuildFilter
     * @return array
     */
    public function buildRangeFilterDataProvider()
    {
        return [
            'rangeFilter' => [
                'filter' => $this->createRangeFilter('range1', 0, 10),
                'expectedResult' => '(range1 >= 0 AND range1 < 10)',
            ]
        ];
    }

    public function buildTermFilterDataProvider()
    {
        return [
            'termFilter' => [
                'filter' => $this->createTermFilter('term1', 123),
                'expectedResult' => '(term1 = 123)',
            ],
        ];
    }

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
                'expectedResult' => '((term1 = 1) AND (range1 >= 0 AND range1 < 10))',
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
                'expectedResult' => '(((term1 = 1) OR (range1 >= 0 AND range1 < 10)))',
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
                'expectedResult' => '(!((term1 = 1) AND (range1 >= 0 AND range1 < 10)))',
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
                'expectedResult' => '((term1 = 1) AND (range1 >= 0 AND range1 < 10)'
                    . ' AND ((term2 = 1) OR (range2 >= 0 AND range2 < 10))'
                    . ' AND !((term3 = 1) AND (range3 >= 0 AND range3 < 10)))',
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
                'expectedResult' => '((term1 = 1) AND (range1 >= 0 AND range1 < 10)'
                    . ' AND ((term2 = 1) OR (range2 >= 0 AND range2 < 10))'
                    . ' AND !((term3 = 1) AND (range3 >= 0 AND range3 < 10)'
                    . ' AND ((term4 = 1) AND (range4 >= 0 AND range4 < 10)'
                    . ' AND ((term5 = 1) OR (range5 >= 0 AND range5 < 10))'
                    . ' AND !((term6 = 1) AND (range6 >= 0 AND range6 < 10)))'
                    . '))',

            ],
            'boolEmpty' => [
                'filter' => $this->createBoolFilter([], [], []),
                'expectedResult' => '',
            ]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown filter type 'unknownType'
     */
    public function testUnknownFilterType()
    {
        /** @var \Magento\Framework\Search\Request\FilterInterface|\PHPUnit_Framework_MockObject_MockObject $filter */
        $filter = $this->getMockBuilder('Magento\Framework\Search\Request\FilterInterface')
            ->setMethods(['getType'])
            ->getMockForAbstractClass();
        $filter->expects($this->exactly(2))
            ->method('getType')
            ->will($this->returnValue('unknownType'));
        $this->builder->build($filter);
    }

    /**
     * @param $field
     * @param $from
     * @param $to
     * @return \Magento\Framework\Search\Request\Filter\Bool|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createRangeFilter($field, $from, $to)
    {
        $filter = $this->getMockBuilder('Magento\Framework\Search\Request\Filter\Range')
            ->setMethods(['getField', 'getFrom', 'getTo'])
            ->disableOriginalConstructor()
            ->getMock();

        $filter->expects($this->exactly(2))
            ->method('getField')
            ->will($this->returnValue($field));
        $filter->expects($this->once())
            ->method('getFrom')
            ->will($this->returnValue($from));
        $filter->expects($this->once())
            ->method('getTo')
            ->will($this->returnValue($to));
        return $filter;
    }

    /**
     * @param $field
     * @param $value
     * @return \Magento\Framework\Search\Request\Filter\Bool|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTermFilter($field, $value)
    {
        $filter = $this->getMockBuilder('Magento\Framework\Search\Request\Filter\Term')
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
     * @param array $must
     * @param array $should
     * @param array $mustNot
     * @return \Magento\Framework\Search\Request\Filter\Bool|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createBoolFilter(array $must, array $should, array $mustNot)
    {
        $filter = $this->getMockBuilder('Magento\Framework\Search\Request\Filter\Bool')
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
}
