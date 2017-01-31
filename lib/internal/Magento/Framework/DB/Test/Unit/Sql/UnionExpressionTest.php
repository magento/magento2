<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\Sql;

use Magento\Framework\DB\Select;

class UnionExpressionTest extends \PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $sqlMock = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();
        $sqlMock->expects($this->once())
            ->method('assemble')
            ->willReturn('test_assemble');
        $parts = [
            $sqlMock,
            '(test_column)'
        ];
        $model = new \Magento\Framework\DB\Sql\UnionExpression($parts);
        $this->assertEquals('(test_assemble)' . Select::SQL_UNION . '(test_column)', $model->__toString());
    }
}
