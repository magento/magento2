<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConditionManagerTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private $resource;

    /** @var ConditionManager */
    private $conditionManager;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['quote', 'quoteIdentifier'])
            ->getMockForAbstractClass();
        $this->connectionMock->expects($this->any())
            ->method('quote')
            ->willReturnCallback(
                function ($value) {
                    return sprintf('\'%s\'', $value);
                }
            );
        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturnCallback(
                function ($value) {
                    return sprintf('`%s`', $value);
                }
            );

        $this->resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->conditionManager = $objectManager->getObject(
            ConditionManager::class,
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
    public static function wrapBracketsDataProvider()
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
    public static function generateConditionDataProvider()
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
