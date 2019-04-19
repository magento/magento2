<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\Sql;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\UnionExpression;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class UnionExpressionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $type
     * @param string $pattern
     * @param string $expectedSql
     * @return void
     * @dataProvider toStringDataProvider
     */
    public function testToString(string $type, string $pattern, string $expectedSql)
    {
        $objectManager = new ObjectManager($this);

        $sqlMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sqlMock->expects($this->once())
            ->method('assemble')
            ->willReturn('test_assemble');
        $parts = [
            $sqlMock,
            '(test_column)'
        ];
        $model = $objectManager->getObject(
            UnionExpression::class,
            [
                'parts' => $parts,
                'type' => $type,
                'pattern' => $pattern,
            ]
        );

        $this->assertEquals($expectedSql, $model->__toString());
    }

    /**
     * @return array
     */
    public function toStringDataProvider(): array
    {
        return [
            [
                'type' => Select::SQL_UNION,
                'pattern' => '',
                'expectedSql' => "(test_assemble)" . Select::SQL_UNION . "(test_column)",
            ],
            [
                'type' => Select::SQL_UNION,
                'pattern' => 'test_with_pattern %s',
                'expectedSql' => "test_with_pattern (test_assemble)" . Select::SQL_UNION . "(test_column)",
            ],
        ];
    }
}
