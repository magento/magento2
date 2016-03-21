<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConditionManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /** @var \Magento\Framework\Search\Adapter\Mysql\ConditionManager */
    private $conditionManager;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->connectionMock = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->setMethods(['quote', 'quoteIdentifier'])
            ->getMockForAbstractClass();
        $this->connectionMock->expects($this->any())
            ->method('quote')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return sprintf('\'%s\'', $value);
                    }
                )
            );
        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return sprintf('`%s`', $value);
                    }
                )
            );

        $this->resource = $this->getMockBuilder('Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($this->connectionMock));

        $this->conditionManager = $objectManager->getObject(
            'Magento\Framework\Search\Adapter\Mysql\ConditionManager',
            [
                'resource' => $this->resource
            ]
        );
    }

    /**
     * @dataProvider wrapBracketsDataProvider
     * @param $query
     * @param $expectedResult
     */
    public function testWrapBrackets($query, $expectedResult)
    {
        $actualResult = $this->conditionManager->wrapBrackets($query);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Data provider for wrapBrackets test
     *
     * @return array
     */
    public function wrapBracketsDataProvider()
    {
        return [
            'validQuery' => [
                'query' => 'a = b',
                'expectedResult' => '(a = b)',
            ],
            'emptyQuery' => [
                'query' => '',
                'expectedResult' => '',
            ],
            'invalidQuery' => [
                'query' => '1',
                'expectedResult' => '(1)',
            ]
        ];
    }

    public function testCombineQueries()
    {
        $queries = [
            'a = b',
            false,
            true,
            '',
            0,
            'test',
        ];
        $unionOperator = 'AND';
        $expectedResult = 'a = b AND 1 AND 0 AND test';
        $actualResult = $this->conditionManager->combineQueries($queries, $unionOperator);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @dataProvider generateConditionDataProvider
     * @param $field
     * @param $operator
     * @param $value
     * @param $expectedResult
     */
    public function testGenerateCondition($field, $operator, $value, $expectedResult)
    {
        $actualResult = $this->conditionManager->generateCondition($field, $operator, $value);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function generateConditionDataProvider()
    {
        return [
            [
                'field' => 'a',
                'operator' => '=',
                'value' => 1,
                'expectedResult' => '`a` = \'1\'',
            ],
            [
                'field' => 'a',
                'operator' => '=',
                'value' => '123',
                'expectedResult' => '`a` = \'123\''
            ],
        ];
    }
}
