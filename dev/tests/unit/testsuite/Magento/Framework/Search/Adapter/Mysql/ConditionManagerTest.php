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

namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\TestFramework\Helper\ObjectManager;

class ConditionManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /** @var \Magento\Framework\Search\Adapter\Mysql\ConditionManager */
    private $conditionManager;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->adapter = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->setMethods(['quote', 'quoteIdentifier'])
            ->getMockForAbstractClass();
        $this->adapter->expects($this->any())
            ->method('quote')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return sprintf('\'%s\'', $value);
                    }
                )
            );
        $this->adapter->expects($this->any())
            ->method('quoteIdentifier')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return sprintf('`%s`', $value);
                    }
                )
            );

        $this->resource = $this->getMockBuilder('Magento\Framework\App\Resource')
            ->disableOriginalConstructor()
            ->setMethods(
                ['getConnection']
            )
            ->getMock();
        $this->resource->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($this->adapter));

        $this->conditionManager = $objectManager->getObject(
            '\Magento\Framework\Search\Adapter\Mysql\ConditionManager',
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
            'test'
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
                'expectedResult' => '`a` = \'1\''
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
